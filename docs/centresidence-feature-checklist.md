# Centresidence — Feature Checklist & Build Registry

> **The single source of truth reconciling the partnership proposal ↔ the code.** Every feature the deck promises appears here with its real build status, so the pitch can never drift from reality and nothing is forgotten. This *is* the "building list of everything incomplete in the finance ecosystem."
>
> **Legend:** ✅ built & working · 🟡 partial / built-but-not-live · ⬜ not started · 🔒 blocked on a decision
> Last updated **2026-07-31**. Companion to `centresidence-system-guide.md` (how it works) and the findings log in `centresidence-hardening-backlog` (memory).

---

## 1. Core platform — what makes the closed loop real
| Feature | Status | Note |
|---|---|---|
| Transaction-mode rent routing (rent → Centresidence account) | ✅ | `centresidence_rent_mpesa_account_id` |
| Deduct-at-source settlement (fee + infra + facility split) | ✅ | `RentSettlementService` / `DeductionEngine`, 60% cap |
| Financing lifecycle (application → underwriting → facility → collection) | ✅ | full state machine + feasibility gate |
| Facility collections / default detection / penalties | ✅ | `FacilityCollectionsService` |
| Partner remittance + payout (M-Pesa B2C/B2B) | 🟡 | built; **live driver + confirmation callback** ⬜ |
| Owner wallet + withdrawals (B2C) | ✅ | **CBK custody question** 🔒 (move float to bank) |
| Metering / device provisioning (gas, water, locks) | 🟡 | works on **simulated** ChirpStack; **live adapter** ⬜ |
| Live token purchase path (tenant buys units) | ⬜ | Finding #3 — engine only, no route/UI/STK |
| Billing cycle (infra + commission invoices) | ✅ | scheduled monthly in provider |
| Subscription-owner infra **collection** | ⬜ | Finding #1 — billed but not charged |

## 2. The 12 verticals (proposal) — build status by tier
**Tier 1 · Rent-secured**
| # | Vertical | Status | What exists / what's missing |
|---|---|---|---|
| 01 | Reticulated Gas Finance | 🟡 | financing + metering built; live tokens & ChirpStack ⬜; wholesale-gas supply ⬜ |
| 02 | Digital Water Finance | 🟡 | as above |
| 03 | Solar Installation Finance | ⬜ | no solar module; **solar-as-service** field-data lead logged (future) |
| 04 | Security Systems Finance | 🟡 | non-metered module path exists; contractor mgmt ⬜ |
| 05 | Repair & Refurbishment Loans | ⬜ | pure financing (no device) — needs non-infra facility type |
| 06 | Cash-Flow Apt. Expansion | ⬜ | non-infra facility + milestone draws |
| 07 | Off-Plan Property Financing | ⬜ | collection-agent + milestone tracking |
| 08 | Owner Personal Loans | ⬜ | pure financing; `settlement_target='owner'` seam ready |
| 10 | Invoice Factoring (service providers) | ⬜ | needs service-provider accounts + invoice engine |
| 12 | Insurance Products | ⬜ | premium-collection + partner underwriting |

**Tier 2 · Data-scored consumer credit**
| # | Vertical | Status | Note |
|---|---|---|---|
| 09 | Tenant Credit & RNPL | ⬜ | **premium-over-rent** repayment mechanism; consumer-credit underwriting |

**Platform services**
| # | Vertical | Status | Note |
|---|---|---|---|
| 11 | Financial Products Marketplace | 🟡 | partner-module marketplace exists; full multi-institution offer/compare ⬜ |

## 3. Data advantage & partner tooling
| Feature | Status | Note |
|---|---|---|
| Rent-payment / occupancy / tenure data | ✅ | legacy platform, genuine |
| Utility consumption / token-pattern data | 🟡 | depends on live tokens (⬜) |
| Real-time partner dashboard (occupancy, payments, portfolio health) | 🟡 | `FinanceAnalyticsService` + partner portal skeleton; bank-grade dashboard ⬜ |
| Credit-file / data export to partner | ⬜ | needed for apt-expansion, owner loans, off-plan |
| Bank API / core-banking integration | ⬜ | onboarding Step 2 (post-pilot) |

## 4. Risk-mitigation mechanisms (proposal §C)
| Claim | Status | Note |
|---|---|---|
| Closed-loop collection | ✅ | real & demonstrable |
| Real-time monitoring / early-warning flags | 🟡 | analytics exist; live bank-facing flags ⬜ |
| Utility data = occupancy proof | 🟡 | needs live tokens |
| Data-verified credit decisions | 🟡 | underwriting engine exists; external-data credit file ⬜ |
| Contractual cash-flow split | ✅ mechanism / ⬜ legal | split works; registered legal instrument ⬜ |

## 5. Revenue mechanisms
| Stream | Status | Note |
|---|---|---|
| Installed-system platform markup on facility | ✅ | `FinancingCalculator` (base + markup) |
| Recurring metering / infra fees | ✅ | (transaction: from rent; subscription: collection ⬜) |
| Utility (gas) commission | ✅ | `TokenEngine` (live tokens ⬜) |
| Origination fee per facility (**bank-paid**) | ⬜ | new — not captured today |
| Servicing / life-of-facility collection fee | ⬜🔒 | the "don't sell short" lever — **per-partner configurable**; queued sitting |
| Affiliate marketplace cut | ✅ | scoped to our commission (Finding #4 fixed) |

## 6. Onboarding & partner integration (proposal §D)
| Step | Status |
|---|---|
| Partner provisioning (create finance partners) | ✅ |
| Partner portal (financier-lens modules + market stats + KB) | ✅ |
| Bi-party framework agreement (legal) | ⬜ (business) |
| Technical integration (API/dashboard) | ⬜ |
| Pilot vertical calibration | 🔒 awaiting bank's pick |

## 7. Cross-cutting hardening (from the systems review)
| Item | Status |
|---|---|
| CBK money-holding architecture (custody → bank) | 🔒 decision + counsel |
| Subscription infra collection (Finding #1) | ⬜ |
| Live token path + ChirpStack live adapter (Finding #3) | ⬜ |
| Affiliate balance refactor (Finding #5) | ⬜ deferred (needs harness + migration) |
| `commission → infra` rename refactor | ⬜ queued |

---

### How to use this file
- **Before a partner conversation:** anything not ✅ is "roadmap we calibrate in the pilot," never claimed as live.
- **When picking build work:** 🔒 items need a decision first; 🟡 items are the fastest wins (finish, don't start); ⬜ are net-new scope.
- **Keep it current:** update the status column in the same PR that changes the behaviour.
