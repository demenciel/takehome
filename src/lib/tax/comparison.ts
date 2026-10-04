import { Money, formatPercent } from '../money';
import type { PayFrequency } from '../frequency';
import type { Province } from '../province';
import { calculatePayroll } from './payroll';
import type { PayrollInputs, PayrollResult } from './types';

export interface PayrollComparison {
  baseline: PayrollResult;
  modified: PayrollResult;
  gross_delta: Money;
  federal_delta: Money;
  provincial_delta: Money;
  tax_delta: Money;
  cpp_delta: Money;
  cpp2_delta: Money;
  ei_delta: Money;
  qpip_delta: Money;
  pension_delta: Money;
  insurance_delta: Money;
  net_annual_delta: Money;
  keep_percent: string;
  incremental_deduction_rate: string;
}

export function comparePayroll(
  province: Province,
  baselineAnnual: Money,
  modifiedAnnual: Money,
  frequency: PayFrequency = 'annual',
  sharedInputs: Partial<PayrollInputs> = {},
): PayrollComparison {
  const baseline = calculatePayroll({
    ...sharedInputs,
    annual_salary: baselineAnnual.dollars(),
    province: province.code,
    frequency,
  });
  const modified = calculatePayroll({
    ...sharedInputs,
    annual_salary: modifiedAnnual.dollars(),
    province: province.code,
    frequency,
  });

  const gross = modified.inputs.annual_salary.subtract(baseline.inputs.annual_salary);
  const federal = modified.metrics.federal_tax.subtract(baseline.metrics.federal_tax);
  const provincial = modified.metrics.provincial_tax.subtract(baseline.metrics.provincial_tax);
  const cpp = modified.metrics.cpp.subtract(baseline.metrics.cpp);
  const cpp2 = modified.metrics.cpp2.subtract(baseline.metrics.cpp2);
  const ei = modified.metrics.ei.subtract(baseline.metrics.ei);
  const qpip = modified.metrics.qpip.subtract(baseline.metrics.qpip);
  const net = modified.metrics.net_annual.subtract(baseline.metrics.net_annual);
  const tax = federal.add(provincial);
  const pension = cpp.add(cpp2);
  const insurance = ei.add(qpip);

  return {
    baseline,
    modified,
    gross_delta: gross,
    federal_delta: federal,
    provincial_delta: provincial,
    tax_delta: tax,
    cpp_delta: cpp,
    cpp2_delta: cpp2,
    ei_delta: ei,
    qpip_delta: qpip,
    pension_delta: pension,
    insurance_delta: insurance,
    net_annual_delta: net,
    keep_percent: formatPercent(net.cents, gross.cents),
    incremental_deduction_rate: formatPercent(gross.cents - net.cents, gross.cents),
  };
}
