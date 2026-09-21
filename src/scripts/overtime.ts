import { estimateOvertime } from '../lib/overtime';
import { Money } from '../lib/money';
import { provinceFromCode } from '../lib/province';
import type { PayFrequency } from '../lib/frequency';
import { formChecked, formValue, showError, showResult, table } from './form';

export function initOvertime(root: ParentNode = document) {
  root.querySelectorAll<HTMLFormElement>('[data-overtime-form]').forEach((form) => {
    const container = form.closest('[data-overtime]') ?? form.parentElement;
    if (!container) return;

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      try {
        const province = provinceFromCode(formValue(form, 'province'));
        if (!province) throw new Error('Select a province or territory.');
        const output = estimateOvertime(
          province,
          Money.fromDollars(formValue(form, 'hourlyWage') || '0'),
          Number(formValue(form, 'regularHours') || '0'),
          Number(formValue(form, 'overtimeHours') || '0'),
          (formValue(form, 'frequency') || 'weekly') as PayFrequency,
          Number(formValue(form, 'doubleHours') || '0'),
          formChecked(form, 'contractPremium'),
        );
        showResult(
          container,
          `
            <h2 class="font-serif text-2xl">Estimated overtime</h2>
            <p class="mt-3 font-serif text-4xl">${output.after_tax_overtime.format()} <span class="text-lg text-ink-soft">after tax this period</span></p>
            ${table('This pay period', [
              ['Regular pay', output.regular_pay.format()],
              ['Gross overtime', output.overtime_pay.format()],
              ['Total gross', output.total_gross.format()],
              ['After-tax overtime', output.after_tax_overtime.format()],
            ])}
            <p class="mt-4 text-sm text-ink-soft">${output.disclaimer}</p>
          `,
        );
      } catch (caught) {
        showError(container, caught instanceof Error ? caught.message : 'Enter valid overtime hours.');
      }
    });
  });
}
