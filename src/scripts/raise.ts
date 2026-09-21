import { Money } from '../lib/money';
import { provinceFromCode } from '../lib/province';
import { comparePayroll } from '../lib/tax/comparison';
import { formValue, showError, showResult, table } from './form';

export function initRaise(root: ParentNode = document) {
  root.querySelectorAll<HTMLFormElement>('[data-raise-form]').forEach((form) => {
    const container = form.closest('[data-raise]') ?? form.parentElement;
    if (!container) return;

    const syncMode = () => {
      const percent = formValue(form, 'raiseMode') === 'percent';
      form.querySelectorAll<HTMLElement>('[data-new-salary]').forEach((el) => {
        el.hidden = percent;
      });
      form.querySelectorAll<HTMLElement>('[data-raise-percent]').forEach((el) => {
        el.hidden = !percent;
      });
    };

    form.addEventListener('change', (event) => {
      if ((event.target as HTMLElement).getAttribute('name') === 'raiseMode') syncMode();
    });

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      try {
        const province = provinceFromCode(formValue(form, 'province'));
        if (!province) throw new Error('Select a province or territory.');
        const current = Money.fromDollars(formValue(form, 'currentSalary') || '0');
        const next =
          formValue(form, 'raiseMode') === 'percent'
            ? current.multiply(String(1 + Number(formValue(form, 'raisePercent') || '0') / 100))
            : Money.fromDollars(formValue(form, 'newSalary') || '0');
        const output = comparePayroll(province, current, next);
        showResult(
          container,
          `
            <h2 class="font-serif text-2xl">Estimated net raise</h2>
            <p class="mt-3 font-serif text-4xl">${output.net_annual_delta.format()} <span class="text-lg text-ink-soft">per year</span></p>
            <p class="mt-2 text-sm text-ink-soft">${output.keep_percent}% of the raise kept · ${output.net_annual_delta.divideBy(12).format()} / month</p>
            ${table('Before / after', [
              ['Current take-home', output.baseline.metrics.net_annual.format()],
              ['New take-home', output.modified.metrics.net_annual.format()],
              ['Gross raise', output.gross_delta.format()],
              ['Net raise', output.net_annual_delta.format()],
              ['Monthly extra', output.net_annual_delta.divideBy(12).format()],
              ['Biweekly extra', output.net_annual_delta.divideBy(26).format()],
            ])}
          `,
        );
      } catch (caught) {
        showError(container, caught instanceof Error ? caught.message : 'Enter a valid raise.');
      }
    });

    syncMode();
  });
}
