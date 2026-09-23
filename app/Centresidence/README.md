# Centresidence — Infrastructure & Finance OS (module)

This namespace (`App\Centresidence`) is the **decoupled module** implementing the
Centresidence Developer Handbook v3 on top of the existing rental SaaS. It is
deliberately self-contained so the live product (rent, invoices, subscriptions,
M-Pesa) can never be destabilised by work in progress here.

## Boundaries (non-negotiable)
- **Owns** its migrations (`app/Centresidence/database/migrations`), config
  (`config/centresidence.php`), models, services ("engines") and events.
- **Reuses, never mutates** legacy tables: `users` (owners), `properties`,
  `property_units`, `invoices`. We reference them by FK; we do not alter them.
- **Multi-tenant = logical multi-owner, single DB.** No stancl DB-per-tenant.

## Core principles (from the handbook)
1. Don't hardcode modules — cost components, token configs, platform fees are
   admin-managed rows, not code.
2. Keep devices generic — a device is an infrastructure endpoint, not a
   utility-specific object.
3. Build simulation first — prove multi-owner topology, cost-component
   aggregation and the dual-pathway fallback before any real hardware.
4. Think in engines — Module, Infrastructure Cost, Finance, Settlement,
   Deployment, Device, Token, Messaging, Rules, Cashflow Analytics.
5. Protect tenant continuity — `is_fallback_eligible` is the only mechanism by
   which platform cost is recovered from token revenue; non-metered costs are
   NEVER token-deducted.
6. Money is exact — all amounts flow through `Support\Money` (integer minor
   units + bcmath). Never do float arithmetic on money.

## Build order (work packages)
- **WP0** Module scaffold, config, `Money`. ✅
- **WP1** Module & catalogue core (`modules`, `property_modules`,
  `module_cost_components`, `module_pricing_catalogue`,
  `module_platform_fee_config`, `module_token_config`). ✅
- **WP2** Device/gateway registry + `infrastructure_topology`.
- **WP3** Infrastructure Cost Engine + Commission Engine.
- **WP4** Token Engine + embedded commission + fallback.
- **WP5** Simulation harness + the 4 handbook success criteria as tests (gate).
- **WP6–WP10** Finance product/application/facility/settlement, defaults,
  analytics, deployment, then real LoRaWAN.

## Deviations from the handbook (intentional, documented)
- **§7.2 token formula corrected.** The handbook prints
  `owner_revenue_per_token_unit = units_per_kes − commission_per_token_unit`,
  which mixes units-per-KES with KES-per-unit. The §7.3 worked example is the
  source of truth; the correct relationship is:

  ```
  price_per_unit            = 1 / units_per_kes           (KES per unit)
  owner_revenue_per_unit    = price_per_unit − centresidence_commission_per_unit
  ```

  Implemented in `Models\ModuleTokenConfig::computeOwnerRevenuePerUnit()`.
- **Generic table names** (`modules`, `property_modules`, …) follow the
  handbook's 38-table index. PHP-side they are namespaced models, so there is
  no collision with the legacy app.
