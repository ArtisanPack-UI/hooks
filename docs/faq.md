---
title: FAQ
---

Frequently asked questions about ArtisanPack UI Hooks.

## Does this work outside Laravel?
The helper functions themselves are framework-agnostic, but this package is designed and tested for Laravel usage with service providers, facades, and Blade directives.

## How do I control execution order?
Use priorities. Lower numbers run first. See [[Priorities and Execution Order]].

## Should I use actions or events?
Laravel Events and Listeners are great for application-wide contracts. Hooks provide a lightweight, plugin-style mechanism that’s convenient for packages and modular extensions. You can use both.

## Can I register multiple callbacks on the same hook?
Yes. They will run in priority order; callbacks with the same priority run in the order they were added.

## How do I test filters?
Pass an initial value to `applyFilters()` in tests and assert the transformed output. See [[Testing]].

## Where can I find examples?
Check the code samples in [[Getting Started]], [[Actions]], [[Filters]], and [[Blade Directives]].

---
Return to [[Home]].
