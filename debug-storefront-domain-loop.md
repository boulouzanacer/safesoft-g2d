[OPEN] Debug session: storefront-domain-loop

## Symptom
- When clicking a boutique (that has a custom domain configured) from the main marketplace website, the browser loops (multiple redirects) and ends with “page unavailable”.

## Expected
- Clicking a boutique from the marketplace should open the boutique storefront (preferably via its custom domain) without redirect loops.

## Hypotheses (falsifiable)
1. The custom domain request is not being resolved by `ResolveStorefrontDomain`, so the app treats it as a platform host and redirects/canonicalizes back, creating a loop.
2. HTTPS / proxy mismatch (Cloudflare → origin) triggers repeated 301/302 (e.g., http↔https or www↔root) resulting in a redirect loop.
3. The selected “preferred domain” is not the actually working/verified one (wrong `is_primary`/`is_active`), causing redirect chains or an unreachable host.
4. A middleware/controller is redirecting to the wrong home URL in domain-mode (e.g., `url('/')` becomes the platform domain due to APP_URL/trusted proxies), causing a bounce loop.
5. Cloudflare Redirect Rules are configured in a way that bounces between two URLs (domain ↔ www, or domain ↔ groslink), but only when arriving via certain paths.

## Evidence to collect
- For each request: host, scheme, path, query, matched custom domain record, boutique id, and response type (normal vs redirect + Location).
- Whether Laravel “thinks” the request is secure (`$request->isSecure()`), and what it generates for `url('/')`.

## Plan
1. Add minimal instrumentation in `ResolveStorefrontDomain` to emit debug events.
2. Deploy instrumentation to VPS.
3. Reproduce by clicking from marketplace, collect logs, identify which hypothesis matches.
4. Apply minimal fix, then re-run reproduction and compare pre-fix/post-fix logs.

