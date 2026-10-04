import { estimateBenefits, type EiProgram } from '../lib/family/benefits';
import { Money } from '../lib/money';
import { formValue, showError, showResult, table } from './form';

export function initEi(root: ParentNode = document) {
  root.querySelectorAll<HTMLFormElement>('[data-ei-form]').forEach((form) => {
    const container = form.closest('[data-ei]') ?? form.parentElement;
    if (!container) return;

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      try {
        const program = (formValue(form, 'program') || 'maternity') as EiProgram;
        const output = estimateBenefits(
          Money.fromDollars(formValue(form, 'salary') || '0'),
          program,
          Number(formValue(form, 'weeks') || '15'),
        );
        showResult(
          container,
          `
            <h2 class="font-serif text-2xl">Estimated EI benefit</h2>
            <p class="mt-3 font-serif text-4xl">${output.weekly_benefit.format()} <span class="text-lg text-ink-soft">/ week</span></p>
            ${table('Benefit', [
              ['Weekly benefit', output.weekly_benefit.format()],
              ['Weeks used', String(output.weeks)],
              ['Total', output.total.format()],
              ['Weekly maximum', output.max_weekly.format()],
            ])}
          `,
        );
      } catch (caught) {
        showError(container, caught instanceof Error ? caught.message : 'Enter a valid EI scenario.');
      }
    });
  });
}
