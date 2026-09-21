import { Money } from '../lib/money';
import { provinceFromCode } from '../lib/province';
import { comparePayroll } from '../lib/tax/comparison';
import { formValue, showError, showResult, table } from './form';

export function initBonus(root: ParentNode = document) {
  root.querySelectorAll<HTMLFormElement>('[data-bonus-form]').forEach((form) => {
    const container = form.closest('[data-bonus]') ?? form.parentElement;
    if (!container) return;

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      try {
        const province = provinceFromCode(formValue(form, 'province'));
        if (!province) throw new Error('Select a province or territory.');
        const salary = Money.fromDollars(formValue(form, 'salary') || '0');
        const bonus = Money.fromDollars(formValue(form, 'bonus') || '0');
        const output = comparePayroll(province, salary, salary.add(bonus));
        showResult(
          container,
          `
            <h2 class="font-serif text-2xl">Estimated net bonus</h2>
            <p class="mt-3 font-serif text-4xl">${output.net_annual_delta.format()}</p>
            <p class="mt-2 text-sm text-ink-soft">${output.keep_percent}% of the bonus kept after incremental tax and contributions.</p>
            ${table('Incremental effect', [
              ['Gross bonus', output.gross_delta.format()],
              ['Extra income tax', output.tax_delta.format()],
              ['CPP/QPP impact', output.pension_delta.format()],
              ['EI/QPIP impact', output.insurance_delta.format()],
              ['Net bonus', output.net_annual_delta.format()],
            ])}
          `,
        );
      } catch (caught) {
        showError(container, caught instanceof Error ? caught.message : 'Enter a valid bonus amount.');
      }
    });
  });
}
