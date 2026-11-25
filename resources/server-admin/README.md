# Server admin pilot

Open `/server-admin/black-friday` as a super admin. This checker only reads
WordPress and the promotion snapshot; it does not enable or run the promotion.

Build with Node 20.19+ or 22.12+:

```sh
npm --prefix resources/server-admin ci
npm --prefix resources/server-admin run build
```

In the Laravel Forge deployment script, run both commands after
`yarn production` and before `$ACTIVATE_RELEASE()`. Keep the existing Laravel
Mix build. To share the script with branches that do not yet contain the
checker, use:

```sh
if [ -f resources/server-admin/package-lock.json ]; then
    npm --prefix resources/server-admin ci --include=dev || exit 1
    npm --prefix resources/server-admin run build || exit 1
fi
```

Locally, use the build commands above and open the page through Herd.
`npm --prefix resources/server-admin run dev` rebuilds the assets on change.
