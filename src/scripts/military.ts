import { calculateMilitarySalary, militaryIncrements, militaryPayLevels, militaryRanks, type MilitaryComponent } from '../lib/military';
import { Money } from '../lib/money';
import { provinceFromCode } from '../lib/province';
import type { PayFrequency } from '../lib/frequency';
import { formValue, showError, showResult, table } from './form';

function fillSelect(select: HTMLSelectElement, options: Array<{ value: string; label: string }>, current: string) {
  select.innerHTML = options
    .map((option) => `<option value="${option.value}" ${option.value === current ? 'selected' : ''}>${option.label}</option>`)
    .join('');
}

export function initMilitary(root: ParentNode = document) {
  root.querySelectorAll<HTMLFormElement>('[data-military-form]').forEach((form) => {
    const container = form.closest('[data-military]') ?? form.parentElement;
    if (!container) return;
    const rankSelect = form.querySelector<HTMLSelectElement>('[name="rank"]');
    const levelSelect = form.querySelector<HTMLSelectElement>('[name="payLevel"]');
    const incrementSelect = form.querySelector<HTMLSelectElement>('[name="increment"]');
    const reserveFields = form.querySelector<HTMLElement>('[data-reserve-fields]');

    const sync = () => {
      const component = (formValue(form, 'component') || 'regular') as MilitaryComponent;
      if (reserveFields) reserveFields.hidden = component !== 'reserve';
      if (!rankSelect || !incrementSelect) return;
      const ranks = militaryRanks(component);
      const rank = ranks.some((item) => item.key === rankSelect.value) ? rankSelect.value : ranks[0].key;
      fillSelect(
        rankSelect,
        ranks.map((item) => ({ value: item.key, label: item.name })),
        rank,
      );
      const levels = militaryPayLevels(component, rank);
      const level = levels.includes(levelSelect?.value ?? '') ? (levelSelect?.value ?? levels[0]) : levels[0];
      if (levelSelect) {
        fillSelect(
          levelSelect,
          levels.map((item) => ({ value: item, label: item })),
          level,
        );
        levelSelect.closest<HTMLElement>('[data-level-fields]')!.hidden = levels.length < 2;
      }
      const increments = Object.keys(militaryIncrements(component, rank, level));
      const increment = increments.includes(incrementSelect.value) ? incrementSelect.value : increments[0];
      fillSelect(
        incrementSelect,
        increments.map((item) => ({ value: item, label: item === 'basic' ? 'Basic' : `Pay increment ${item}` })),
        increment,
      );
    };

    form.addEventListener('change', (event) => {
      const name = (event.target as HTMLElement).getAttribute('name');
      if (name === 'component' || name === 'rank' || name === 'payLevel') sync();
    });

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      try {
        const province = provinceFromCode(formValue(form, 'province'));
        if (!province) throw new Error('Select a province or territory.');
        const output = calculateMilitarySalary({
          component: (formValue(form, 'component') || 'regular') as MilitaryComponent,
          rank: formValue(form, 'rank'),
          increment: formValue(form, 'increment'),
          province,
          frequency: (formValue(form, 'frequency') || 'monthly') as PayFrequency,
          payLevel: formValue(form, 'payLevel'),
          reserveDays: Number(formValue(form, 'reserveDays') || '37'),
          taxableAllowances: formValue(form, 'allowances') ? Money.fromDollars(formValue(form, 'allowances')) : null,
        });
        showResult(
          container,
          `
            <h2 class="font-serif text-2xl">${output.component_label} · ${output.pay.rank_name}</h2>
            <p class="mt-3 font-serif text-4xl">${output.payroll.metrics.net_annual.format()} <span class="text-lg text-ink-soft">annual take-home</span></p>
            ${table('Pay and deductions', [
              ['Official base rate', `${output.pay.rate.format()} / ${output.pay.unit}`],
              ['Annualized base pay', output.base_annual.format()],
              ['Pension', output.pension.amount.format()],
              ['Federal tax', output.payroll.metrics.federal_tax.format()],
              ['Provincial tax', output.payroll.metrics.provincial_tax.format()],
              ['Annual take-home', output.payroll.metrics.net_annual.format()],
            ])}
            <p class="mt-4 text-sm text-ink-soft">${output.pension.note} Pay tables effective ${output.effective_date}.</p>
          `,
        );
      } catch (caught) {
        showError(container, caught instanceof Error ? caught.message : 'Enter a valid CAF pay selection.');
      }
    });

    sync();
  });
}
