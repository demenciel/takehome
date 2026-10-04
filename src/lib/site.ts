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
  { name: 'Founder Triage', href: 'https://triage.alexworks.app' },
  { name: 'MCP X-Ray', href: 'https://mcpxray.alexworks.app' },
  { name: 'Alex Works', href: 'https://alexworks.app' },
  { name: 'Print Ready Check', href: 'https://print.alexworks.app' },
] as const;
