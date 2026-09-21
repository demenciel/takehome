export function formValue(form: HTMLFormElement, name: string): string {
  const field = form.elements.namedItem(name);
  if (field instanceof RadioNodeList) {
    return String(field.value ?? '');
  }
  if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
    return field.value;
  }
  return '';
}

export function formChecked(form: HTMLFormElement, name: string): boolean {
  const field = form.elements.namedItem(name);
  return field instanceof HTMLInputElement ? field.checked : false;
}

export function showError(container: ParentNode, message: string) {
  const error = container.querySelector<HTMLElement>('[data-error]');
  const result = container.querySelector<HTMLElement>('[data-result]');
  if (result) result.hidden = true;
  if (error) {
    error.hidden = false;
    error.textContent = message;
  }
}

export function showResult(container: ParentNode, html: string) {
  const error = container.querySelector<HTMLElement>('[data-error]');
  const result = container.querySelector<HTMLElement>('[data-result]');
  if (error) error.hidden = true;
  if (result) {
    result.hidden = false;
    result.innerHTML = html;
  }
}

export function table(title: string, rows: Array<[string, string]>): string {
  return `
    <table class="data-table mt-6">
      <thead><tr><th>${title}</th><th class="text-right">Amount</th></tr></thead>
      <tbody>
        ${rows.map(([label, value]) => `<tr><th>${label}</th><td class="text-right">${value}</td></tr>`).join('')}
      </tbody>
    </table>
  `;
}
