User-agent: *
Allow: /
Disallow: /admin
Disallow: /admin/

# Calculator inputs are application state, not indexable URLs.
# Canonical tags on public pages ignore query strings.

Sitemap: {{ route('sitemap') }}
