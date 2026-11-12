// Example vehicles for the screenshots of the feature page, in the export format of the app (VehicleExport,
// formatVersion 1). Everything here is made up. Dates are relative to today, so "due" and "overdue" look the same on
// every run; names exist in German and English, so the English screenshots show English data.

export const texts = {
  de: {
    bike: 'MT-07',
    car: 'Familienkombi',
    ebike: 'Pendler-E-Bike',
    oil: 'Ölwechsel mit Filter',
    oilDesc: 'Motoröl 10W-40, 3,1 l mit Filter',
    chain: 'Kette reinigen und schmieren',
    valves: 'Ventilspiel prüfen',
    brakeFluid: 'Bremsflüssigkeit wechseln',
    plugs: 'Zündkerzen wechseln',
    airFilter: 'Luftfilter wechseln',
    tires: 'Reifen prüfen',
    mirror: 'Spiegelhalter festziehen',
    inspection: 'Inspektion',
    carOil: 'Ölwechsel',
    hu: 'Hauptuntersuchung',
    cabin: 'Innenraumfilter wechseln',
    ebChain: 'Kette schmieren',
    ebBrakes: 'Bremsbeläge prüfen',
    ebService: 'Jahresinspektion',
    engineOil: 'Motoröl 10W-40',
    oilFilter: 'Ölfilter',
    gasket: 'Dichtring Ablassschraube',
    chainLube: 'Kettenspray',
    shim: 'Shim 1,90',
    brakeFluidMat: 'Bremsflüssigkeit DOT 4',
    sparkPlug: 'Zündkerze',
    workshop: 'Werkstatt Müller, Inspektion nach Plan',
    valveNote: 'Ventilspiel gemessen, Auslass Zylinder 2 zu eng – Shim getauscht',
    springNote: 'Saisonstart',
    discOk: 'gut',
    fluidOk: 'klar, voll',
    sprocketOk: 'gut',
    tireOk: 'gleichmäßig abgefahren',
  },
  en: {
    bike: 'MT-07',
    car: 'Family estate',
    ebike: 'Commuter e-bike',
    oil: 'Oil change with filter',
    oilDesc: 'Engine oil 10W-40, 3.1 l with filter',
    chain: 'Clean and lube chain',
    valves: 'Check valve clearance',
    brakeFluid: 'Change brake fluid',
    plugs: 'Replace spark plugs',
    airFilter: 'Replace air filter',
    tires: 'Check tyres',
    mirror: 'Tighten mirror mount',
    inspection: 'Service',
    carOil: 'Oil change',
    hu: 'Roadworthiness test',
    cabin: 'Replace cabin filter',
    ebChain: 'Lube chain',
    ebBrakes: 'Check brake pads',
    ebService: 'Annual service',
    engineOil: 'Engine oil 10W-40',
    oilFilter: 'Oil filter',
    gasket: 'Drain plug washer',
    chainLube: 'Chain spray',
    shim: 'Shim 1.90',
    brakeFluidMat: 'Brake fluid DOT 4',
    sparkPlug: 'Spark plug',
    workshop: 'Local workshop, scheduled service',
    valveNote: 'Valve clearance measured, exhaust cylinder 2 too tight – shim replaced',
    springNote: 'Start of the season',
    discOk: 'good',
    fluidOk: 'clear, full',
    sprocketOk: 'good',
    tireOk: 'evenly worn',
  },
};

/** Date `months` months and `days` days before `today`, as yyyy-mm-dd. */
function ago(today, months, days = 0) {
  const d = new Date(Date.UTC(today.getUTCFullYear(), today.getUTCMonth() - months, today.getUTCDate() - days));
  return d.toISOString().slice(0, 10);
}

function file(vehicle, today) {
  return { formatVersion: 1, exportedAt: today.toISOString(), appVersion: 'screenshots', vehicle };
}

function motorcycle(t, today) {
  return file(
    {
      name: t.bike,
      type: 'MOTORCYCLE',
      make: 'Yamaha',
      model: 'MT-07',
      modelYear: 2019,
      firstRegistrationDate: '2019-04-12',
      currentMileage: 38450,
      specifications: {
        cylinders: 2,
        valvesPerCylinder: 4,
        valveAdjustment: 'SHIM',
        intakeClearanceMin: 0.11,
        intakeClearanceMax: 0.2,
        exhaustClearanceMin: 0.21,
        exhaustClearanceMax: 0.3,
        shimStep: 0.05,
        shimMin: 1.2,
        shimMax: 3.2,
        brakePadMinFront: 1,
        brakePadMinRear: 1,
        brakeDiscMinFront: 4,
        brakeDiscMinRear: 4.5,
        chainSlackMin: 50,
        chainSlackMax: 55,
        chainWearLinks: 20,
        chainWearMaxLength: 319.4,
        tirePressureFront: 2.25,
        tirePressureRear: 2.5,
        treadDepthMin: 1.6,
      },
      maintenanceTasks: [
        { ref: 't1', name: t.oil, description: t.oilDesc, intervalKm: 10000, intervalMonths: 12 },
        { ref: 't2', name: t.chain, intervalKm: 1000 },
        { ref: 't3', name: t.valves, intervalKm: 40000 },
        { ref: 't4', name: t.brakeFluid, intervalMonths: 24 },
        { ref: 't5', name: t.plugs, intervalKm: 20000 },
        { ref: 't6', name: t.airFilter, intervalKm: 40000 },
        { ref: 't7', name: t.tires, intervalMonths: 3 },
        { ref: 't8', name: t.mirror, oneTime: true },
      ],
      maintenanceLogs: [
        {
          performedAt: ago(today, 23, 10),
          mileageAtPerformed: 26800,
          currency: 'EUR',
          performedTaskRefs: ['t4'],
          laborCost: 35,
          materials: [{ name: t.brakeFluidMat, quantity: 0.5, unit: 'LITER', price: 9.9, taskRef: 't4' }],
        },
        {
          performedAt: ago(today, 18),
          mileageAtPerformed: 28950,
          currency: 'EUR',
          notes: t.valveNote,
          performedTaskRefs: ['t3', 't5'],
          materials: [
            { name: t.sparkPlug, partNumber: 'NGK LMAR8A-9', quantity: 2, unit: 'PIECE', price: 24.8, taskRef: 't5' },
            { name: t.shim, quantity: 1, unit: 'PIECE', price: 6.5, taskRef: 't3' },
          ],
          measurements: {
            valveClearance: {
              valves: [
                { cylinder: 1, valve: 'INTAKE', index: 1, measured: 0.15, shimOld: 1.9 },
                { cylinder: 1, valve: 'INTAKE', index: 2, measured: 0.16, shimOld: 1.9 },
                { cylinder: 1, valve: 'EXHAUST', index: 1, measured: 0.25, shimOld: 1.85 },
                { cylinder: 1, valve: 'EXHAUST', index: 2, measured: 0.24, shimOld: 1.85 },
                { cylinder: 2, valve: 'INTAKE', index: 1, measured: 0.14, shimOld: 1.95 },
                { cylinder: 2, valve: 'INTAKE', index: 2, measured: 0.15, shimOld: 1.9 },
                { cylinder: 2, valve: 'EXHAUST', index: 1, measured: 0.18, shimOld: 1.95, shimNew: 1.9 },
                { cylinder: 2, valve: 'EXHAUST', index: 2, measured: 0.23, shimOld: 1.9 },
              ],
            },
          },
        },
        {
          performedAt: ago(today, 11, 5),
          mileageAtPerformed: 29400,
          currency: 'EUR',
          notes: t.workshop,
          performedTaskRefs: ['t1', 't7'],
          laborCost: 60,
          materials: [
            { name: t.engineOil, quantity: 3.1, unit: 'LITER', price: 38.5, taskRef: 't1' },
            { name: t.oilFilter, partNumber: '5GH-13440-61', quantity: 1, unit: 'PIECE', price: 12.9, taskRef: 't1' },
            { name: t.gasket, quantity: 1, unit: 'PIECE', price: 1.5, taskRef: 't1' },
          ],
          measurements: {
            tires: {
              pressureFront: 2.2,
              pressureRear: 2.4,
              treadFront: 3.1,
              treadRear: 2.2,
              dotFront: '1423',
              dotRear: '1423',
              conditionRear: t.tireOk,
            },
          },
        },
        {
          performedAt: ago(today, 2, 3),
          mileageAtPerformed: 37200,
          currency: 'EUR',
          notes: t.springNote,
          performedTaskRefs: ['t2', 't7', 't8'],
          createdTaskRefs: ['t8'],
          materials: [{ name: t.chainLube, quantity: 1, unit: 'PIECE', price: 11.9, taskRef: 't2' }],
          measurements: {
            chain: { slack: 58, wearLength: 318.2, wearLinks: 20, sprocketCondition: t.sprocketOk, cleaned: true, lubricated: true },
            brakes: { padFront: 2.8, padRear: 3.5, discFront: 4.6, discRear: 5, discConditionFront: t.discOk, fluidCondition: t.fluidOk },
          },
        },
      ],
    },
    today,
  );
}

function car(t, today) {
  return file(
    {
      name: t.car,
      type: 'CAR',
      make: 'Skoda',
      model: 'Octavia Combi',
      modelYear: 2020,
      firstRegistrationDate: '2020-09-01',
      currentMileage: 87300,
      maintenanceTasks: [
        { ref: 't1', name: t.inspection, intervalKm: 30000, intervalMonths: 24 },
        { ref: 't2', name: t.carOil, intervalKm: 15000, intervalMonths: 12 },
        { ref: 't3', name: t.hu, intervalMonths: 24 },
        { ref: 't4', name: t.cabin, intervalMonths: 12 },
      ],
      maintenanceLogs: [
        {
          performedAt: ago(today, 23, 20),
          mileageAtPerformed: 61200,
          currency: 'EUR',
          performedTaskRefs: ['t3'],
          laborCost: 142.6,
        },
        {
          performedAt: ago(today, 13),
          mileageAtPerformed: 72100,
          currency: 'EUR',
          notes: t.workshop,
          performedTaskRefs: ['t1', 't2', 't4'],
          laborCost: 289,
          materials: [{ name: t.engineOil.replace('10W-40', '5W-30'), quantity: 4.7, unit: 'LITER', price: 61.1, taskRef: 't2' }],
        },
      ],
    },
    today,
  );
}

function eBike(t, today) {
  return file(
    {
      name: t.ebike,
      type: 'E_BIKE',
      make: 'Cube',
      model: 'Kathmandu Hybrid',
      modelYear: 2023,
      firstRegistrationDate: '2023-03-18',
      currentMileage: 6240,
      maintenanceTasks: [
        { ref: 't1', name: t.ebChain, intervalKm: 300 },
        { ref: 't2', name: t.ebBrakes, intervalMonths: 6 },
        { ref: 't3', name: t.ebService, intervalMonths: 12 },
      ],
      maintenanceLogs: [
        { performedAt: ago(today, 10), mileageAtPerformed: 4100, currency: 'EUR', performedTaskRefs: ['t3'], laborCost: 89 },
        { performedAt: ago(today, 0, 12), mileageAtPerformed: 6100, currency: 'EUR', performedTaskRefs: ['t1', 't2'] },
      ],
    },
    today,
  );
}

/** The example vehicles in the given language ("de" or "en"); the motorcycle first, it is the main example. */
export function exampleVehicles(lang, today = new Date()) {
  const t = texts[lang];
  return [motorcycle(t, today), car(t, today), eBike(t, today)];
}

/** Profile names for the household-mode screenshot. */
export const exampleProfiles = { de: ['Anna', 'Ben', 'Werkstatt'], en: ['Anna', 'Ben', 'Garage'] };
