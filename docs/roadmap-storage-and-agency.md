# Roadmap — Storage Spaces & Agency Capabilities

> Status: **planned**, to start **after Centresidence module testing is finished**.
> Scope: **legacy product side** (properties / units / users / packages) — independent of the
> Centresidence module. Own branch + PR; does not touch the Centresidence WIP.
> Sequence: **Storage first** (self-contained), then **Agency** (more design surface).

## Why these fit with minimal disruption
The app already gives us the seams:
- **Tenant isolation is `owner_user_id`** — nearly everything is `where('owner_user_id', auth()->id())`.
- **Leasing/rent-flow hang off `property_units`** — a unit already carries rent, deposit, lease
  dates, rent type, tenant, invoices.

So both features are **additive**: a type flag on units, an agent flag + link on the owner side,
and *new parallel screens* — never a rewrite of the `owner_user_id` scoping.

---

## Feature 1 — Storage spaces

**Approach:** a storage space is a rentable unit of a different type — reuse the unit engine.
- Add `space_type` to `property_units`: `residential | commercial | storage` (default `residential` → backward compatible).
- Storage units reuse leasing, tenants, invoices and **rent flow** unchanged.

**Known deltas to design (from owner input):**
- [ ] **Different pricing** for storage (its own rent/deposit basis; possibly a storage-specific package/price tier — ties into the package `pricing_model`/per-unit pricing system).
- [ ] **Slightly different units UI** — hide residential fields (bedroom/bath/kitchen), add storage fields (size, access type, climate-controlled?), distinct listing/badge.
- [ ] **Slightly different "tenants" UI** — a storage occupant isn't a household; trim/relabel the tenant form & detail for storage lettings.

**Reused as-is:** invoices, payments, rent flow, lease dates, deposits, overdue logic.
**Disruption:** LOW — one column + UI conditionals + pricing tweak.

### Build checklist (draft)
- [ ] Migration: `property_units.space_type` (+ any storage-only fields)
- [ ] Unit add/edit form: type selector + conditional fields
- [ ] Unit listing: storage filter/badge/label
- [ ] Tenant (occupant) form/detail: storage variant labels/fields
- [ ] Pricing: storage rent basis + package/tier handling
- [ ] Reports/dashboards: storage included in rent flow (verify, likely free)

---

## Feature 2 — Agency capabilities

**Approach:** same owner account, flagged as an agent; the real owner is linked per property.
- `users.is_agent` (boolean) — agent uses a normal owner account, just flagged.
- `properties.actual_owner_user_id` (nullable FK → users) — the real owner when an agent manages
  the property. **Null = today's behaviour** (the `owner_user_id` IS the real owner), so every
  existing property keeps working.

Then:
- **Agent property views** show the actual owner's name (join when `actual_owner_user_id` set).
- **Actual owners** see **rent flow** via a NEW **read-only "landlord/portfolio" view** scoped by
  `actual_owner_user_id = me` → their property set → invoices/payments on those properties.

**The one rule that keeps disruption low:** the actual-owner view is **additive and parallel**
(a narrow read-only portal). Do NOT make the existing `owner_user_id`-scoped screens multi-owner
aware — the property data stays under the agent's account; the actual owner just gets a read-only
window into their properties' rent flow.

**Known deltas to design (from owner input):**
- [ ] **Different pricing** for agencies (agent package tier; actual-owner accounts likely
      **exempt from subscription** since they're principals, not managers — needs a bypass flag).
- [ ] **Small UI tweaks** — agent-specific labels/columns (actual owner name), an "actual owner"
      assignment control on properties, and the actual-owner read-only portal screens.

**Disruption:** MODERATE but contained — two additive columns + agent labels + one read-only portal.

### Open decisions (lock before building)
1. **Storage identity:** unit-type flag *(recommended)* vs separate storage entity.
2. **Actual-owner identity:** real linked owner account + read-only portal *(recommended — gives a
   real login/portal)* vs lightweight name-only record on the property (no login; statements only).
3. **Agency/actual-owner billing:** confirm actual owners bypass subscription; define the agent tier.

### Build checklist (draft)
- [ ] Migration: `users.is_agent`, `properties.actual_owner_user_id`
- [ ] Account: agent flag set at signup/settings; agent badge in UI
- [ ] Property add/edit: assign/create actual owner (when account is an agent)
- [ ] Agent property list/detail: show actual owner name
- [ ] New read-only "actual owner" portal: properties + rent flow scoped by `actual_owner_user_id`
- [ ] Subscription gate: exempt actual-owner accounts; agent pricing tier
- [ ] Permissions: actual owner is read-only, sees only their linked properties

---

## Pre-build audit (fill while going through the current system)
Note current touchpoints that need a storage/agency variant. Suggested areas to walk:
- [ ] Property add/edit + unit add/edit forms & validation (`PropertyController`, requests)
- [ ] Unit/tenant listing + detail views
- [ ] Invoice / rent-flow / dashboard queries that assume `owner_user_id` only
- [ ] Package/subscription & `pricing_model` (per-unit pricing, new tiers) — see admin packages
- [ ] Signup / account settings (where `is_agent` would be set)
- [ ] Any per-unit counts/limits enforced by package (storage units counted? separately?)
- [ ] Notifications / reports that should include storage or route to actual owners

## Guardrails (carry over from the Centresidence approach)
- Additive only: new columns default to today's behaviour; existing rows/flows unchanged.
- Never rewire `owner_user_id` scoping — add parallel read-only views instead.
- Own branch + PR, separate from Centresidence; small, reviewable commits.
- Migrations backward-compatible; the live rental product must behave exactly as before for
  non-storage / non-agency accounts.
