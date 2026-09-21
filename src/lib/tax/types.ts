import type { Money } from '../money';
import type { PayFrequency } from '../frequency';
import type { Province, ProvinceCode } from '../province';

export interface Bracket {
  threshold: string;
  rate: string;
  constant: string;
}

export interface PensionResult {
  contribution: Money;
  second_additional: Money;
  base: Money;
  additional: Money;
}

export interface FederalTaxResult {
  t1: Money;
  t3: Money;
  k1: Money;
  k2: Money;
  k4: Money;
  bpa: Money;
  bracket_rate: string;
}

export interface ProvincialTaxResult {
  t2: Money;
  t4: Money;
  k1p: Money;
  k2p: Money;
  surtax: Money;
  health_premium: Money;
  reduction: Money;
  bpa: Money;
}

export interface BreakdownRow {
  key: string;
  label: string;
  amount: Money;
  kind: 'gross' | 'deduction' | 'net';
}

export interface PayrollInputs {
  province: string;
  frequency?: PayFrequency | string;
  tax_year?: number;
  annual_salary?: number | string;
  input_mode?: 'annual' | 'hourly';
  hourly_wage?: number | string;
  hours_per_week?: number | string;
  rrsp?: number | string;
  pension?: number | string;
  union_dues?: number | string;
  other_deductions?: number | string;
  additional_tax?: number | string;
}

export interface PayrollResult {
  summary: string;
  headlineAmount: Money;
  headlinePeriod: string;
  inputs: {
    annual_salary: Money;
    province: ProvinceCode;
    province_name: string;
    frequency: PayFrequency;
    tax_year: number;
    rrsp: Money;
    pension: Money;
    union_dues: Money;
    other_deductions: Money;
    additional_tax_per_period: Money;
  };
  periodBreakdown: BreakdownRow[];
  annualBreakdown: BreakdownRow[];
  metrics: {
    effective_tax_rate: string;
    average_deductions: Money;
    federal_tax: Money;
    provincial_tax: Money;
    taxable_income: Money;
    cpp: Money;
    cpp2: Money;
    ei: Money;
    qpip: Money;
    net_annual: Money;
    net_period: Money;
  };
  warnings: string[];
  metadata: {
    calculator: string;
    tax_year: number;
    edition: string | null;
    claim_code: number;
    federal_bpa: Money;
    provincial_bpa: Money;
  };
  shareText: string;
  copyText: string;
  province: Province;
}

export interface SourceLink {
  title: string;
  url: string;
  publisher?: string;
  used_for?: string;
}

export interface TaxYearRules {
  year: number;
  federal: Record<string, unknown>;
  cpp: Record<string, unknown>;
  qpp: Record<string, unknown>;
  ei: Record<string, unknown>;
  qpip: Record<string, unknown>;
  provinces: Record<ProvinceCode, Record<string, unknown>>;
  sources: {
    tax_year: number;
    edition: string;
    effective_date: string;
    retrieved_date: string;
    notes: string[];
    sources: SourceLink[];
  };
}
