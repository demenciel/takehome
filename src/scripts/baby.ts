import { babyRecurringDefaults, babyStartupDefaults, summarizeBaby } from '../lib/family/baby';
import { formChecked, formValue, showError, showResult, table } from './form';

export function initBaby(root: ParentNode = document) {
  root.querySelectorAll<HTMLFormElement>('[data-baby-form]').forEach((form) => {
    const container = form.closest('[data-baby]') ?? form.parentElement;
    if (!container) return;

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      try {
        const startup = babyStartupDefaults().map((item) => ({
          ...item,
          planned: formValue(form, `startup-${item.id}`) || item.planned,
          skip: formChecked(form, `skip-${item.id}`),
          gift: formChecked(form, `gift-${item.id}`),
        }));
        const recurring = babyRecurringDefaults().map((item) => ({
          ...item,
          planned: formValue(form, `recurring-${item.id}`) || item.planned,
        }));
        const output = summarizeBaby(startup, recurring, {
          needed: formChecked(form, 'childcareNeeded'),
          monthly: formValue(form, 'childcareMonthly'),
          start_month: formValue(form, 'childcareStart') || '1',
        }, {
          ccb_monthly: formValue(form, 'ccbMonthly'),
          current_savings: formValue(form, 'currentSavings'),
          monthly_saved: formValue(form, 'monthlySaved'),
          months_until: formValue(form, 'monthsUntil') || '0',
        });
        showResult(
          container,
          `
            <h2 class="font-serif text-2xl">Planning total</h2>
            <p class="mt-3 font-serif text-4xl">${output.amount_left_to_prepare.format()}</p>
            <p class="mt-2 text-sm text-ink-soft">Estimated amount left to prepare after savings and CCB you entered.</p>
            ${table('Baby budget', [
              ['Remaining purchases', output.remaining_purchases.format()],
              ['Monthly recurring', output.monthly_recurring.format()],
              ['First-year cost before childcare', output.first_year_cost.format()],
              ['Projected savings', output.projected_savings.format()],
            ])}
          `,
        );
      } catch (caught) {
        showError(container, caught instanceof Error ? caught.message : 'Enter valid baby-cost amounts.');
      }
    });
  });
}
