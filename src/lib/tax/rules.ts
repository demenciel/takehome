import type { Province, ProvinceCode } from '../province';
import type { TaxYearRules } from './types';
import federal2026 from '../../data/tax/2026/federal.json';
import cpp2026 from '../../data/tax/2026/cpp.json';
import qpp2026 from '../../data/tax/2026/qpp.json';
import ei2026 from '../../data/tax/2026/ei.json';
import qpip2026 from '../../data/tax/2026/qpip.json';
import sources2026 from '../../data/tax/2026/sources.json';
import alberta from '../../data/tax/2026/alberta.json';
import britishColumbia from '../../data/tax/2026/british_columbia.json';
import manitoba from '../../data/tax/2026/manitoba.json';
import newBrunswick from '../../data/tax/2026/new_brunswick.json';
import newfoundland from '../../data/tax/2026/newfoundland_and_labrador.json';
import novaScotia from '../../data/tax/2026/nova_scotia.json';
import ontario from '../../data/tax/2026/ontario.json';
import pei from '../../data/tax/2026/prince_edward_island.json';
import quebec from '../../data/tax/2026/quebec.json';
import saskatchewan from '../../data/tax/2026/saskatchewan.json';
import northwestTerritories from '../../data/tax/2026/northwest_territories.json';
import nunavut from '../../data/tax/2026/nunavut.json';
import yukon from '../../data/tax/2026/yukon.json';

export const CURRENT_TAX_YEAR = 2026;

const YEARS: Record<number, TaxYearRules> = {
  2026: {
    year: 2026,
    federal: federal2026 as Record<string, unknown>,
    cpp: cpp2026 as Record<string, unknown>,
    qpp: qpp2026 as Record<string, unknown>,
    ei: ei2026 as Record<string, unknown>,
    qpip: qpip2026 as Record<string, unknown>,
    provinces: {
      AB: alberta as Record<string, unknown>,
      BC: britishColumbia as Record<string, unknown>,
      MB: manitoba as Record<string, unknown>,
      NB: newBrunswick as Record<string, unknown>,
      NL: newfoundland as Record<string, unknown>,
      NS: novaScotia as Record<string, unknown>,
      ON: ontario as Record<string, unknown>,
      PE: pei as Record<string, unknown>,
      QC: quebec as Record<string, unknown>,
      SK: saskatchewan as Record<string, unknown>,
      NT: northwestTerritories as Record<string, unknown>,
      NU: nunavut as Record<string, unknown>,
      YT: yukon as Record<string, unknown>,
    },
    sources: sources2026,
  },
};

export function taxRulesForYear(year = CURRENT_TAX_YEAR): TaxYearRules {
  const rules = YEARS[year];

  if (!rules) {
    throw new Error(`No tax rules for ${year}.`);
  }

  return rules;
}

export function provinceRules(province: Province, year = CURRENT_TAX_YEAR): Record<string, unknown> {
  return taxRulesForYear(year).provinces[province.code as ProvinceCode];
}

export function taxFreshness(year = CURRENT_TAX_YEAR) {
  const sources = taxRulesForYear(year).sources;
  const retrieved = sources.retrieved_date ?? sources.effective_date;

  return {
    year: sources.tax_year,
    edition: sources.edition,
    effectiveDate: sources.effective_date,
    retrievedDate: retrieved,
    lastUpdatedLabel: formatDateLabel(retrieved),
    notes: sources.notes,
    sources: sources.sources,
  };
}

export function formatDateLabel(iso: string): string {
  const date = new Date(`${iso}T00:00:00Z`);

  return date.toLocaleDateString('en-CA', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
    timeZone: 'UTC',
  });
}
