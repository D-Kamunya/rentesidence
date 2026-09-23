# Centresidence — Agency Inclusion (vision doc)

> **Provenance & status.** Author: the product owner (fished from the vision board, shared 2026-09-05).
> This is a STRATEGIC VISION input for the deferred **agency sitting** — it is **subject to review, not scripture.**
> Everything here must be **aligned to what already exists in this codebase**; systems already built
> (owner accounts, financing, screening/Global Tenant ID, marketplace, credit rail, dispatch/maintainer)
> should *fit into* this model, not be discarded for it. Pair with the memory note `agency-system-design`.

---

## From Property Management Software → the Operating System for Property Management

**Thesis:** Centresidence should position as the **operating system, marketplace, and trust infrastructure**
for property management — NOT primarily as a traditional property-management company. A traditional PM company
scales by acquiring properties + employing people (capacity tied to headcount). Centresidence scales as
infrastructure through which properties everywhere are managed.

### Three interconnected pillars
1. **Centresidence Software** — the property-management operating system.
2. **Centresidence Marketplace** — connects property owners with property **managers** and service providers.
3. **Centresidence Managed** — a *premium, controlled* direct-management service (a service layer, NOT the primary identity).

### The strategic question
Owners keep asking *"Can you manage the property for us?"* — many have an **execution** problem, not just a tech problem.
Three owner groups to serve, without becoming a traditional PM company:
- **Self-Managed** — owner runs it themselves on the software (rent, tenants, arrears, invoices, maintenance,
  utilities, records, reports, financing, services). Centresidence = technology provider; earns software + transaction revenue.
- **Professionally Managed** — owner appoints a manager from a **verified marketplace**, compares them on
  **measurable performance data**, and the chosen manager operates the property through Centresidence.
  Centresidence = technology + marketplace + **trust layer**. Revenue: marketplace fees, SaaS, transaction &
  payment-processing, service commissions, financing referrals.
- **Centresidence Managed** — premium direct management for owners who want a single accountable operator.
  Centresidence = property manager + technology provider. Revenue: management fees + software/transaction/ecosystem.

### Proposed strategic statement
> Centresidence is the operating system for property management — empowering owners and professional managers with
> the technology, marketplace, payments, services and financial infrastructure to operate properties efficiently.
> Through its property-manager marketplace it connects owners with verified professionals and uses **real
> operational performance data to create a trusted property-management reputation network**. Through Centresidence
> Managed it can also provide premium direct management. The goal is not to manage every property ourselves — it is
> to become the infrastructure through which properties everywhere can be managed.

---

## Review lens — how this aligns to what's ALREADY built (to resolve at the sitting)

- **"Professionally Managed" ≈ the existing agency recommendation.** The `agency-system-design` model
  (landlord = permanent first-class entity that OWNS the properties; agency = operator under a **revocable
  mandate**; owner gets a claimable read-mostly **transparency view**; fallout-safe, no orphans) is the concrete
  *mechanism* for this pillar. The doc adds the **marketplace + discovery/compare/appoint** layer on top of that bilateral model.
- **★ The genuinely new, high-leverage idea = a property-MANAGER reputation network.** This is the **same engine
  as the Global Tenant ID / screening** (objective behaviour data → portable, explainable reputation), just pointed
  at **managers/agencies** (collection rate, arrears handling, response times, occupancy, remittance timeliness)
  instead of tenants. Strong reuse of built infra + a real moat. Managers become rated ecosystem participants.
- **Centresidence Managed** = new premium tier; relates to the recurring "manage for us" demand and the deferred
  guided/managed-service ideas. Keep it a *controlled* layer so it doesn't cannibalise the marketplace or become the identity.
- **Open crux still governs (unchanged): CBK / money-flow.** Rent flowing owner→(agency collects, takes commission,
  remits)→owner is the deposit-taking question. Saving grace: managers/agencies are licensed/registered (that's the
  validation gate); does rent settle to the agency (we ledger the split) or split-at-source? Still the open decision.
- **The real tension (from 2026-08-30):** most of our moat/infra assumes an **owner with real property CONTROL +
  OWNERSHIP rights** (financing, infra/meter deployment, disbursement). Agencies sit awkwardly on top of that; the
  marketplace framing doesn't dissolve that friction — it must be reconciled, not glossed.

See `agency-system-design` (central pool), `global-tenant-id-vision` (reputation engine to reuse),
`psp-partner-posture` / `centresidence-money-flow` (CBK), `plan-sitting-prep` (agency plan bands, gated on this).
