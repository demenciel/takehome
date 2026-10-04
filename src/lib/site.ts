export const SITE_NAME = 'Paycheque.app';
export const CONTACT_EMAIL = import.meta.env.PUBLIC_CONTACT_EMAIL || 'hello@paycheque.app';
export const LEGAL_NAME = import.meta.env.PUBLIC_LEGAL_NAME || SITE_NAME;
export const ANALYTICS_MEASUREMENT_ID = import.meta.env.PUBLIC_ANALYTICS_MEASUREMENT_ID || 'G-YVZRWG4BSV';
export const ANALYTICS_ENABLED = import.meta.env.PUBLIC_ANALYTICS_ENABLED !== 'false';
export const ADS_ENABLED = import.meta.env.PUBLIC_ADS_ENABLED === 'true';
export const ADS_PROVIDER = import.meta.env.PUBLIC_ADS_PROVIDER || '';
export const ADS_CLIENT = import.meta.env.PUBLIC_ADS_CLIENT || '';
export const PRIVACY_UPDATED = '2026-09-21';
export const TERMS_UPDATED = '2026-09-05';
export const ADSENSE_LOADER_CLIENT = 'ca-pub-9601080087531926';

export const OTHER_PROJECTS = [
  { name: 'Founder Triage', host: 'triage.alexworks.app', origin: 'https://triage.alexworks.app', track: true },
  { name: 'MCP X-Ray', host: 'mcpxray.alexworks.app', origin: 'https://mcpxray.alexworks.app', track: true },
  { name: 'Alex Works', host: 'alexworks.app', origin: 'https://alexworks.app', track: false },
  { name: 'Print Ready Check', host: 'print.alexworks.app', origin: 'https://print.alexworks.app', track: false },
] as const;

export function projectHref(
  project: (typeof OTHER_PROJECTS)[number],
  content: 'footer' | 'about',
): string {
  if (!project.track) {
    return project.origin;
  }

  const url = new URL(project.origin);
  url.searchParams.set('utm_source', 'paycheque.app');
  url.searchParams.set('utm_medium', 'referral');
  url.searchParams.set('utm_campaign', 'site-backlink');
  url.searchParams.set('utm_content', content);
  return url.toString();
}
