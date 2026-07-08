// TypeScript 6 defaults noUncheckedSideEffectImports to true; CSS side-effect
// imports (main.ts) need a module shim to typecheck.
declare module '*.css' {}
