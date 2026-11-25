# Server admin pilot

Open `/server-admin/black-friday` as a super admin. This checker only reads
WordPress and the promotion snapshot; it does not enable or run the promotion.

Use Node 20.19+ or 22.12+. Forge and local production builds use the same
commands from the repository root:

```sh
yarn install --frozen-lockfile
yarn production
```

The root build runs Mix first, then installs the checker's pinned dependencies
and builds its Vite assets. Keep `yarn production` before
`$ACTIVATE_RELEASE()` in Forge. Remove the standalone checker build block if
it was added to Forge previously.

To build only the checker locally:

```sh
npm --prefix resources/server-admin ci --include=dev
npm --prefix resources/server-admin run build
```

Open the page locally through Herd.
`npm --prefix resources/server-admin run dev` rebuilds the assets on change.
