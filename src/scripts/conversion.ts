import { annualFromHourly, assumptionLabel, hourlyFromAnnual } from '../lib/hourly';
import { Money } from '../lib/money';
import { formValue, showError, showResult, table } from './form';

export function initConversion(root: ParentNode = document) {
  root.querySelectorAll<HTMLFormElement>('[data-conversion-form]').forEach((form) => {
    const container = form.closest('[data-conversion]') ?? form.parentElement;
    if (!container) return;
    const mode = form.dataset.mode === 'salary_to_hourly' ? 'salary_to_hourly' : 'hourly_to_salary';

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      try {
        const hours = Number(formValue(form, 'hoursPerWeek') || '40');
        if (mode === 'salary_to_hourly') {
          const salary = Money.fromDollars(formValue(form, 'salary') || '0');
          const hourly = hourlyFromAnnual(salary, hours);
          showResult(
            container,
            `
              <h2 class="font-serif text-2xl">Gross hourly equivalent</h2>
              <p class="mt-3 font-serif text-4xl">${hourly.format()} <span class="text-lg text-ink-soft">/ hour</span></p>
              ${table('Conversion', [
                ['Annual salary', salary.format()],
                ['Hours assumption', assumptionLabel(hours)],
                ['Hourly equivalent', hourly.format()],
              ])}
            `,
          );
          return;
        }

        const hourly = Money.fromDollars(formValue(form, 'hourlyWage') || '0');
        const annual = annualFromHourly(hourly, hours);
        showResult(
          container,
          `
            <h2 class="font-serif text-2xl">Gross annual equivalent</h2>
            <p class="mt-3 font-serif text-4xl">${annual.format()}</p>
            ${table('Conversion', [
              ['Hourly wage', hourly.format()],
              ['Hours assumption', assumptionLabel(hours)],
              ['Annual salary', annual.format()],
            ])}
          `,
        );
      } catch (caught) {
        showError(container, caught instanceof Error ? caught.message : 'Enter a valid amount.');
      }
    });
  });
}
