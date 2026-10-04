import { SITE_NAME } from './site';
import { absoluteUrl } from './catalog';

export interface FaqItem {
  question: string;
  answer: string;
}

export interface BreadcrumbItem {
  name: string;
  url: string;
}

export interface SeoPage {
  title: string;
  description: string;
  canonical: string;
  breadcrumbs?: BreadcrumbItem[];
  faqs?: FaqItem[];
  includeWebsite?: boolean;
  includeApplication?: boolean;
  ogTitle?: string;
  ogDescription?: string;
}

export function jsonLd(seo: SeoPage) {
  const graph: Record<string, unknown>[] = [
    {
      '@type': 'Organization',
      name: SITE_NAME,
      url: absoluteUrl('/'),
    },
    {
      '@type': 'WebPage',
      name: seo.title,
      description: seo.description,
      url: seo.canonical,
    },
  ];

  if (seo.includeWebsite) {
    graph.push({
      '@type': 'WebSite',
      name: SITE_NAME,
      url: absoluteUrl('/'),
    });
  }

  if (seo.includeApplication !== false) {
    graph.push({
      '@type': 'WebApplication',
      name: SITE_NAME,
      applicationCategory: 'FinanceApplication',
      operatingSystem: 'Any',
      url: seo.canonical,
    });
  }

  if (seo.breadcrumbs?.length) {
    graph.push({
      '@type': 'BreadcrumbList',
      itemListElement: seo.breadcrumbs.map((item, index) => ({
        '@type': 'ListItem',
        position: index + 1,
        name: item.name,
        item: item.url,
      })),
    });
  }

  if (seo.faqs?.length) {
    graph.push({
      '@type': 'FAQPage',
      mainEntity: seo.faqs.map((faq) => ({
        '@type': 'Question',
        name: faq.question,
        acceptedAnswer: {
          '@type': 'Answer',
          text: faq.answer,
        },
      })),
    });
  }

  return {
    '@context': 'https://schema.org',
    '@graph': graph,
  };
}
