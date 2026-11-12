// Takes the web screenshots of the feature page (bike-maintainer/feature-tour) in German and English. Started by
// run.sh, which also provides the household stack and converts the PNGs to WebP. Needs a running portal-mode instance
// (bike-maintainer-deploy/dev) and an account on it; that account's vehicles are REPLACED by the example vehicles.
//
// Environment: BM_SHOT_URL, BM_SHOT_EMAIL, BM_SHOT_PASSWORD, BM_SHOT_HOUSEHOLD_URL, BM_SHOT_OUT (PNG directory).

import { execFileSync } from 'node:child_process';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { chromium } from 'playwright';
import { exampleProfiles, exampleVehicles, texts } from './seed.mjs';

const base = process.env.BM_SHOT_URL ?? 'http://localhost:8100';
const householdBase = process.env.BM_SHOT_HOUSEHOLD_URL ?? 'http://localhost:8110';
const email = required('BM_SHOT_EMAIL');
const password = required('BM_SHOT_PASSWORD');
const args = process.argv.slice(2);
// "--seed de": only put the example vehicles of that language on the account (used by app.py before each language).
const seedOnly = args[0] === '--seed' ? args[1] : null;
const out = seedOnly ? null : required('BM_SHOT_OUT');
const only = seedOnly ? [] : args; // optional shot names, e.g. "dashboard history"

const ui = {
  de: { locale: 'de-DE', userMenu: 'Benutzermenü' },
  en: { locale: 'en-GB', userMenu: 'User menu' },
};

function required(name) {
  const value = process.env[name];
  if (!value) {
    console.error(`Set ${name}.`);
    process.exit(1);
  }
  return value;
}

function wanted(name) {
  return only.length === 0 || only.includes(name);
}

/** A browser context in the given language, logged in; returns the page and an API helper using its session. */
async function session(browser, lang, url, viewport) {
  const context = await browser.newContext({ viewport, locale: ui[lang].locale, colorScheme: 'dark' });
  await context.addInitScript((l) => localStorage.setItem('bm.language', l), lang);
  const page = await context.newPage();
  const api = async (method, path, data) => {
    const xsrf = (await context.cookies()).find((c) => c.name === 'XSRF-TOKEN')?.value;
    const response = await page.request.fetch(url + path, { method, data, headers: xsrf ? { 'X-XSRF-TOKEN': xsrf } : {} });
    if (!response.ok()) {
      throw new Error(`${method} ${path}: ${response.status()} ${await response.text()}`);
    }
    const type = response.headers()['content-type'] ?? '';
    return type.includes('json') ? response.json() : response.body();
  };
  return { context, page, api };
}

async function settle(page, ms = 600) {
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(ms);
}

async function shot(target, lang, name, options = {}) {
  const dir = join(out, lang);
  mkdirSync(dir, { recursive: true });
  await target.screenshot({ path: join(dir, `${name}.png`), animations: 'disabled', ...options });
  console.log(`  ${lang}/${name}`);
}

/** Upper part of the first page of a PDF from the API as PNG (a whole A4 page would dominate its section). */
async function pdfShot(api, lang, name, path, part = 0.62) {
  const dir = join(out, lang);
  mkdirSync(dir, { recursive: true });
  const pdf = join(dir, `${name}.pdf`);
  writeFileSync(pdf, await api('GET', path));
  execFileSync('pdftoppm', ['-png', '-r', '110', '-f', '1', '-l', '1', '-singlefile', pdf, join(dir, name)]);
  execFileSync('rm', [pdf]);
  const png = join(dir, `${name}.png`);
  execFileSync('magick', [png, '-gravity', 'north', '-crop', `100%x${Math.round(part * 100)}%+0+0`, '+repage', png]);
  console.log(`  ${lang}/${name} (PDF)`);
}

async function portal(browser, lang) {
  const t = texts[lang];
  const { context, page, api } = await session(browser, lang, base, { width: 1280, height: 800 });
  await page.goto(`${base}/anmelden`);
  await page.fill('#login-email', email);
  await page.fill('#login-password', password);
  const [login] = await Promise.all([
    page.waitForResponse((r) => r.url().endsWith('/api/v1/auth/login')),
    page.locator('button[type=submit]').click(),
  ]);
  if (login.status() === 429) {
    // The backend allows 10 logins per 15 minutes and IP; a full run with the app needs 6.
    throw new Error('Login rate limit reached – wait 15 minutes and run again.');
  }
  await page.waitForURL(/dashboard/, { waitUntil: 'commit', timeout: 60000 });

  // Same data for every run: account language for the PDFs, example vehicles instead of whatever was there.
  await api('PATCH', '/api/v1/auth/me', { language: lang, currency: 'EUR' });
  for (const vehicle of await api('GET', '/api/v1/vehicles')) {
    await api('DELETE', `/api/v1/vehicles/${vehicle.id}`);
  }
  const files = exampleVehicles(lang);
  for (const file of files) {
    await api('POST', '/api/v1/vehicles/import', file);
  }
  if (seedOnly) {
    await context.close();
    return;
  }
  const bike = (await api('GET', '/api/v1/vehicles')).find((v) => v.type === 'MOTORCYCLE');
  const bikeUrl = `${base}/fahrzeuge/${bike.id}`;
  const tabs = page.getByRole('tab');

  if (wanted('dashboard')) {
    await page.goto(`${base}/dashboard`);
    await settle(page);
    await shot(page, lang, 'dashboard', { clip: { x: 0, y: 0, width: 1280, height: 650 } });
  }

  if (wanted('status')) {
    await page.goto(bikeUrl);
    await settle(page);
    await shot(page, lang, 'status');
  }

  if (wanted('tasks')) {
    await page.setViewportSize({ width: 1280, height: 1000 });
    await page.goto(bikeUrl);
    await settle(page);
    await tabs.nth(3).click();
    await settle(page);
    // Edit the oil change, so the form shows a real task with both intervals.
    const task = page.locator('mat-card').filter({ hasText: t.oil }).last();
    await task.getByRole('button').filter({ hasText: 'edit' }).first().click();
    await settle(page);
    // The form with the edited task of the list below it.
    const form = page.locator('mat-card').filter({ has: page.locator('input[type=checkbox]') }).first();
    const second = page.locator('mat-card').filter({ hasText: t.oil }).filter({ hasNot: page.locator('input') }).last();
    await page.mouse.move(0, 0);
    await form.scrollIntoViewIfNeeded();
    const top = (await form.boundingBox()).y;
    const bottom = (await second.boundingBox()).y + (await second.boundingBox()).height;
    await shot(page, lang, 'tasks', { clip: { x: 48, y: top - 16, width: 1184, height: bottom - top + 32 } });
    await page.setViewportSize({ width: 1280, height: 800 });
  }

  if (wanted('log') || wanted('measurements')) {
    await page.setViewportSize({ width: 1280, height: 1600 });
    await page.goto(bikeUrl);
    await settle(page);
    await tabs.nth(1).click();
    await settle(page);
    // The oil change: tasks, materials, labour, tyre values – a filled-in form without typing.
    const oil = page.locator('mat-card.log-card').filter({ hasText: t.workshop });
    await oil.getByRole('button').filter({ hasText: 'edit' }).first().click();
    await settle(page);
    if (wanted('log')) {
      // Date, tasks, tyre values and the first material; the form sits in a scroll area, so clip by position.
      const form = page.locator('mat-card').filter({ has: page.locator('input[type=date]') }).first();
      await form.scrollIntoViewIfNeeded();
      const box = await form.boundingBox();
      const material = await page.getByText(/^Material 1$/).first().boundingBox();
      const second = await page.getByText(/^Material 2$/).first().boundingBox();
      const bottom = second ? second.y - 24 : material.y + 330;
      await shot(page, lang, 'log', { clip: { x: box.x, y: box.y, width: box.width, height: bottom - box.y } });
    }
    if (wanted('measurements')) {
      await tabs.nth(1).click();
      await settle(page);
      const valveLog = page.locator('mat-card.log-card').filter({ hasText: t.valveNote });
      await valveLog.getByRole('button').filter({ hasText: 'edit' }).first().click();
      await settle(page);
      const panel = page.locator('section.measure-set').filter({ has: page.locator('select') }).first();
      await shot(panel, lang, 'measurements');
    }
    await page.setViewportSize({ width: 1280, height: 800 });
  }

  if (wanted('history')) {
    await page.setViewportSize({ width: 1280, height: 1400 });
    await page.goto(bikeUrl);
    await settle(page);
    await tabs.nth(1).click();
    await settle(page);
    const first = page.locator('mat-card.log-card').first();
    for (const toggle of await first.getByRole('button').all()) {
      const text = await toggle.innerText();
      if (/expand_more|keyboard_arrow_down/.test(text) || /Messwerte|Measured|Kosten|Costs/.test(text)) {
        await toggle.click();
      }
    }
    await settle(page, 400);
    await shot(first, lang, 'history');
    await page.setViewportSize({ width: 1280, height: 800 });
  }

  if (wanted('report')) {
    await pdfShot(api, lang, 'report', `/api/v1/vehicles/${bike.id}/maintenance-report`);
  }
  if (wanted('worksheet')) {
    await pdfShot(api, lang, 'worksheet', `/api/v1/vehicles/${bike.id}/worksheets/VALVE_CLEARANCE?cylinders=2&valvesPerCylinder=4`);
  }

  if (wanted('import')) {
    await page.goto(`${base}/dashboard`);
    await settle(page);
    await page.getByRole('button', { name: ui[lang].userMenu }).click();
    await page.locator('.shell__import-vehicle').click();
    await settle(page, 300);
    // Named like a real export file of the app.
    const file = join(out, `vehicle-mt-07-${new Date().toISOString().slice(0, 10)}.json`);
    writeFileSync(file, JSON.stringify(files[0], null, 2));
    await page.locator('input[type=file]').setInputFiles(file);
    execFileSync('rm', [file]);
    await page.evaluate(() => document.activeElement?.blur());
    await settle(page);
    await shot(page.locator('mat-dialog-container'), lang, 'import');
  }

  await context.close();
}

async function household(browser, lang) {
  const { context, page, api } = await session(browser, lang, householdBase, { width: 1280, height: 720 });
  await page.goto(`${householdBase}/person-waehlen`);
  await settle(page);
  // Profiles in the language of the screenshot (the stack is shared by both runs).
  for (const profile of await api('GET', '/api/v1/profiles')) {
    await api('DELETE', `/api/v1/profiles/${profile.id}`);
  }
  for (const displayName of exampleProfiles[lang]) {
    await api('POST', '/api/v1/profiles', { displayName });
  }
  await page.reload();
  await settle(page);
  // The profile card with some space around it; the rest of the page is empty.
  const card = await page.locator('mat-card').first().boundingBox();
  await shot(page, lang, 'household', { clip: { x: card.x - 160, y: card.y - 60, width: card.width + 320, height: card.height + 120 } });
  await context.close();
}

const browser = await chromium.launch();
try {
  if (seedOnly) {
    await portal(browser, seedOnly);
  }
  for (const lang of seedOnly ? [] : ['de', 'en']) {
    console.log(`${lang}:`);
    await portal(browser, lang);
    if (wanted('household')) {
      await household(browser, lang);
    }
  }
} finally {
  await browser.close();
}
