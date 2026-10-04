import names from '../data/provinces.json';

export type ProvinceCode =
  | 'AB'
  | 'BC'
  | 'MB'
  | 'NB'
  | 'NL'
  | 'NS'
  | 'ON'
  | 'PE'
  | 'QC'
  | 'SK'
  | 'NT'
  | 'NU'
  | 'YT';

export interface Province {
  code: ProvinceCode;
  name: string;
  adjective: string;
  slug: string;
  usesQpp: boolean;
}

const SLUGS: Record<ProvinceCode, string> = {
  AB: 'alberta',
  BC: 'british-columbia',
  MB: 'manitoba',
  NB: 'new-brunswick',
  NL: 'newfoundland-and-labrador',
  NS: 'nova-scotia',
  ON: 'ontario',
  PE: 'prince-edward-island',
  QC: 'quebec',
  SK: 'saskatchewan',
  NT: 'northwest-territories',
  NU: 'nunavut',
  YT: 'yukon',
};

const ALIASES: Record<string, ProvinceCode> = {
  bc: 'BC',
  britishcolumbia: 'BC',
  nb: 'NB',
  nl: 'NL',
  newfoundland: 'NL',
  'newfoundland-labrador': 'NL',
  ns: 'NS',
  pei: 'PE',
  pe: 'PE',
  nwt: 'NT',
  'northwest-territory': 'NT',
  nt: 'NT',
  nu: 'NU',
  yt: 'YT',
  on: 'ON',
  qc: 'QC',
  ab: 'AB',
  mb: 'MB',
  sk: 'SK',
};

export const PROVINCE_CODES = Object.keys(SLUGS) as ProvinceCode[];

export function allProvinces(): Province[] {
  return PROVINCE_CODES.map((code) => provinceFromCode(code)!);
}

export function provinceFromCode(code: string): Province | null {
  const normalized = code.toUpperCase() as ProvinceCode;

  if (!(normalized in SLUGS)) {
    return null;
  }

  const copy = names[normalized as keyof typeof names];

  return {
    code: normalized,
    name: copy.name,
    adjective: copy.adjective,
    slug: SLUGS[normalized],
    usesQpp: normalized === 'QC',
  };
}

export function provinceFromSlug(slug: string): Province | null {
  const normalized = slug.toLowerCase();
  const match = allProvinces().find((province) => province.slug === normalized);

  if (match) {
    return match;
  }

  const alias = ALIASES[normalized];

  return alias ? provinceFromCode(alias) : null;
}

export function resolveProvince(value: string): Province | null {
  return provinceFromCode(value) ?? provinceFromSlug(value);
}

export function isCanonicalProvinceSlug(slug: string): boolean {
  return allProvinces().some((province) => province.slug === slug);
}

export function provinceAliases(): Record<string, string> {
  return Object.fromEntries(
    Object.entries(ALIASES).map(([alias, code]) => [alias, SLUGS[code]]),
  );
}
