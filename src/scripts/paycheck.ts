import { calculatePayroll } from '../lib/tax/payroll';
import type { PayFrequency } from '../lib/frequency';

function value(form: HTMLFormElement, name: string): string {
  const field = form.elements.namedItem(name);
  if (field instanceof RadioNodeList) {
    return String(field.value ?? '');
  }
  if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement) {
    return field.value;
  }
  return '';
}

function renderResult(container: HTMLElement, form: HTMLFormElement) {
  const error = container.querySelector('[data-error]') as HTMLElement | null;
  const result = container.querySelector('[data-result]') as HTMLElement | null;
  if (error) error.hidden = true;

  try {
    const inputMode = value(form, 'inputMode') === 'hourly' ? 'hourly' : 'annual';
    const output = calculatePayroll({
      province: value(form, 'province'),
      frequency: (value(form, 'frequency') || 'biweekly') as PayFrequency,
      input_mode: inputMode,
      annual_salary: value(form, 'salary'),
      hourly_wage: value(form, 'hourlyWage'),
      hours_per_week: value(form, 'hoursPerWeek') || '40',
      rrsp: value(form, 'rrsp'),
      pension: value(form, 'pension'),
      union_dues: value(form, 'unionDues'),
      other_deductions: value(form, 'otherDeductions'),
      additional_tax: value(form, 'additionalTax'),
    });

    if (!result) return;
    result.hidden = false;
    result.innerHTML = `
      <h2 class="font-serif text-2xl">${output.summary}</h2>
      <p class="mt-3 font-serif text-4xl">${output.headlineAmount.format()} <span class="text-lg text-ink-soft">${output.headlinePeriod}</span></p>
      <p class="mt-2 text-sm text-ink-soft">Effective tax rate ${output.metrics.effective_tax_rate}% · Annual take-home ${output.metrics.net_annual.format()}</p>
      <table class="data-table mt-6">
        <thead><tr><th>Per cheque</th><th class="text-right">Amount</th></tr></thead>
        <tbody>
          ${output.periodBreakdown.map((row) => `<tr><th>${row.label}</th><td class="text-right">${row.amount.format()}</td></tr>`).join('')}
        </tbody>
      </table>
      <table class="data-table mt-6">
        <thead><tr><th>Annual</th><th class="text-right">Amount</th></tr></thead>
        <tbody>
          ${output.annualBreakdown.map((row) => `<tr><th>${row.label}</th><td class="text-right">${row.amount.format()}</td></tr>`).join('')}
        </tbody>
      </table>
      <p class="mt-4 text-sm text-ink-soft">${output.warnings[0] ?? ''}</p>
    `;
  } catch (caught) {
    if (result) result.hidden = true;
    if (error) {
      error.hidden = false;
      error.textContent = caught instanceof Error ? caught.message : 'Enter a valid salary amount.';
    }
  }
}

export function initPaycheck(root: ParentNode = document) {
  root.querySelectorAll<HTMLFormElement>('[data-paycheck-form]').forEach((form) => {
    const container = form.closest('[data-paycheck]') ?? form.parentElement;
    if (!container) return;

    const syncMode = () => {
      const hourly = value(form, 'inputMode') === 'hourly';
      form.querySelectorAll<HTMLElement>('[data-annual-fields]').forEach((el) => {
        el.hidden = hourly;
      });
      form.querySelectorAll<HTMLElement>('[data-hourly-fields]').forEach((el) => {
        el.hidden = !hourly;
      });
    };

    form.addEventListener('change', (event) => {
      if ((event.target as HTMLElement).getAttribute('name') === 'inputMode') {
        syncMode();
      }
    });

    form.querySelector('[data-advanced-toggle]')?.addEventListener('click', () => {
      const panel = form.querySelector<HTMLElement>('[data-advanced]');
      if (panel) panel.hidden = !panel.hidden;
    });

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      renderResult(container as HTMLElement, form);
    });

    syncMode();
    if (form.dataset.autocalc === 'true') {
      renderResult(container as HTMLElement, form);
    }
  });
}

if (typeof document !== 'undefined') {
  initPaycheck();
}
