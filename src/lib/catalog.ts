import { allProvinces, provinceFromCode, type Province, type ProvinceCode } from './province';

export const POPULAR_SALARIES = [
  30000, 35000, 40000, 45000, 50000, 55000, 60000, 65000, 70000, 75000, 80000, 85000, 90000, 100000, 110000, 120000,
  150000,
];

export const EXAMPLE_SALARIES = [40000, 50000, 60000, 70000, 80000, 90000, 100000, 120000, 150000];

export const SALARY_PAGE_PROVINCES: ProvinceCode[] = ['ON', 'QC', 'BC', 'AB', 'NB', 'NS', 'MB'];

export function indexableSalaryProvinces(): Province[] {
  return SALARY_PAGE_PROVINCES.map((code) => provinceFromCode(code)!);
}

export function salaryPageAllowed(province: Province, salary: number): boolean {
  return SALARY_PAGE_PROVINCES.includes(province.code) && POPULAR_SALARIES.includes(salary);
}

export function salaryNeighbors(salary: number): { previous: number | null; next: number | null } {
  const index = POPULAR_SALARIES.indexOf(salary);

  if (index === -1) {
    return { previous: null, next: null };
  }

  return {
    previous: POPULAR_SALARIES[index - 1] ?? null,
    next: POPULAR_SALARIES[index + 1] ?? null,
  };
}

export function salaryCompare(salary: number): number[] {
  return [salary - 10000, salary - 5000, salary, salary + 5000, salary + 10000, salary + 20000].filter((amount) =>
    POPULAR_SALARIES.includes(amount),
  );
}

export const SITE_ORIGIN = 'https://paycheque.app';

export function absoluteUrl(path = '/'): string {
  if (path === '/' || path === '') {
    return SITE_ORIGIN;
  }

  return `${SITE_ORIGIN}${path.startsWith('/') ? path : `/${path}`}`;
}

export function allCanonicalProvinces(): Province[] {
  return allProvinces();
}
