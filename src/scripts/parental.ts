import { projectParentalLeave } from '../lib/family/parental';
import { formChecked, formValue, showError, showResult, table } from './form';

export function initParental(root: ParentNode = document) {
  root.querySelectorAll<HTMLFormElement>('[data-parental-form]').forEach((form) => {
    const container = form.closest('[data-parental]') ?? form.parentElement;
    if (!container) return;

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      try {
        const output = projectParentalLeave({
          province: formValue(form, 'province'),
          salary: formValue(form, 'salary'),
          frequency: formValue(form, 'frequency') || 'biweekly',
          leave_type: formValue(form, 'leaveType') || 'maternity_standard',
          planned_weeks: formValue(form, 'plannedWeeks') || '50',
          partner_weeks: formValue(form, 'partnerWeeks') || '0',
          top_up_enabled: formChecked(form, 'topUpEnabled'),
          top_up_percent: formValue(form, 'topUpPercent') || '0',
          top_up_weeks: formValue(form, 'topUpWeeks') || '0',
        });
        if (!output.supported || output.quebec) {
          showResult(
            container,
            `<h2 class="font-serif text-2xl">Québec uses QPIP</h2><p class="mt-3">Federal EI maternity and parental rates are not applied to Québec. See <a class="font-semibold text-accent-dark underline" href="https://www.rqap.gouv.qc.ca/en">RQAP</a>.</p>`,
          );
          return;
        }
        showResult(
          container,
          `
            <h2 class="font-serif text-2xl">Estimated leave income</h2>
            <p class="mt-3 font-serif text-4xl">${output.weekly_ei.format()} <span class="text-lg text-ink-soft">/ week EI</span></p>
            ${table('Leave estimate', [
              ['Weekly EI', output.weekly_ei.format()],
              ['Monthly EI', output.monthly_ei.format()],
              ['Counted weeks', String(output.leave_weeks)],
              ['Total EI', output.ei_total.format()],
              ['Leave weekly with top-up', output.leave_weekly.format()],
            ])}
            <p class="mt-4 text-sm text-ink-soft">${output.warnings[0] ?? ''}</p>
          `,
        );
      } catch (caught) {
        showError(container, caught instanceof Error ? caught.message : 'Enter a valid leave scenario.');
      }
    });
  });
}
