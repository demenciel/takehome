import { describe, expect, it } from 'vitest';
import { canadaAnswer, salaryAnswer, salaryFaqs } from '../src/lib/answers';
import { jsonLd } from '../src/lib/seo';
import { calculatePayroll } from '../src/lib/tax/payroll';
import { provinceFromCode } from '../src/lib/province';
import { absoluteUrl } from '../src/lib/catalog';

describe('SEO / AEO helpers', () => {
  it('keeps the national answer in static-friendly copy', () => {
    const summary = canadaAnswer();
    expect(summary.question).toBe('How is take-home pay calculated in Canada?');
    expect(summary.answer).toContain('Choose a province');
    expect(summary.year).toBe(2026);
    expect(summary.methodology_url).toBe(absoluteUrl('/methodology'));
  });

  it('builds an Ontario $80,000 salary answer from the payroll engine', () => {
    const province = provinceFromCode('ON')!;
    const result = calculatePayroll({ annual_salary: '80000', province: 'ON', frequency: 'annual' });
    const summary = salaryAnswer(province, 80000, result);
    expect(summary.question).toBe('How much is an $80,000 salary after tax in Ontario?');
    expect(summary.answer).toContain(result.metrics.net_annual.format());
    expect(summary.facts.some((fact) => fact.label === 'Annual net')).toBe(true);
    expect(salaryFaqs(province, 80000, result)[0].question).toBe('How much is $80,000 after tax in Ontario?');
  });

  it('emits WebPage, BreadcrumbList, and FAQ JSON-LD without fake reviews', () => {
    const schema = jsonLd({
      title: '$80,000 Salary After Tax in Ontario',
      description: 'Estimated take-home pay',
      canonical: absoluteUrl('/ontario/80000-salary'),
      breadcrumbs: [
        { name: 'Home', url: absoluteUrl('/') },
        { name: 'Ontario', url: absoluteUrl('/ontario-paycheck-calculator') },
      ],
      faqs: [{ question: 'How much is $80,000 after tax in Ontario?', answer: 'An estimate.' }],
    });
    const types = schema['@graph'].map((node) => node['@type']);
    expect(types).toContain('WebPage');
    expect(types).toContain('BreadcrumbList');
    expect(types).toContain('FAQPage');
    expect(JSON.stringify(schema)).not.toContain('AggregateRating');
    expect(JSON.stringify(schema)).not.toContain('reviewRating');
  });
});
