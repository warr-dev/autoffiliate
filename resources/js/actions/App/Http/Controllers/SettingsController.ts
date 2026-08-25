import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\SettingsController::index
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/settings/app',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SettingsController::index
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::index
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::index
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SettingsController::index
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::index
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::index
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/settings/app'
*/
const update2cd785c5be4fcaa9b72a40c88a729ca5 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update2cd785c5be4fcaa9b72a40c88a729ca5.url(options),
    method: 'post',
})

update2cd785c5be4fcaa9b72a40c88a729ca5.definition = {
    methods: ["post"],
    url: '/settings/app',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/settings/app'
*/
update2cd785c5be4fcaa9b72a40c88a729ca5.url = (options?: RouteQueryOptions) => {
    return update2cd785c5be4fcaa9b72a40c88a729ca5.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/settings/app'
*/
update2cd785c5be4fcaa9b72a40c88a729ca5.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update2cd785c5be4fcaa9b72a40c88a729ca5.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/settings/app'
*/
const update2cd785c5be4fcaa9b72a40c88a729ca5Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update2cd785c5be4fcaa9b72a40c88a729ca5.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/settings/app'
*/
update2cd785c5be4fcaa9b72a40c88a729ca5Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update2cd785c5be4fcaa9b72a40c88a729ca5.url(options),
    method: 'post',
})

update2cd785c5be4fcaa9b72a40c88a729ca5.form = update2cd785c5be4fcaa9b72a40c88a729ca5Form
/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/api/settings'
*/
const update68a6718475f44f9c38857865541e66e3 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update68a6718475f44f9c38857865541e66e3.url(options),
    method: 'post',
})

update68a6718475f44f9c38857865541e66e3.definition = {
    methods: ["post"],
    url: '/api/settings',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/api/settings'
*/
update68a6718475f44f9c38857865541e66e3.url = (options?: RouteQueryOptions) => {
    return update68a6718475f44f9c38857865541e66e3.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/api/settings'
*/
update68a6718475f44f9c38857865541e66e3.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update68a6718475f44f9c38857865541e66e3.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/api/settings'
*/
const update68a6718475f44f9c38857865541e66e3Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update68a6718475f44f9c38857865541e66e3.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/api/settings'
*/
update68a6718475f44f9c38857865541e66e3Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update68a6718475f44f9c38857865541e66e3.url(options),
    method: 'post',
})

update68a6718475f44f9c38857865541e66e3.form = update68a6718475f44f9c38857865541e66e3Form

/**
* Multiple routes resolve to \App\Http\Controllers\SettingsController::update, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `update['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const update = {
    '/settings/app': update2cd785c5be4fcaa9b72a40c88a729ca5,
    '/api/settings': update68a6718475f44f9c38857865541e66e3,
}

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/settings/social-accounts/export'
*/
const exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62.url(options),
    method: 'get',
})

exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62.definition = {
    methods: ["get","head"],
    url: '/settings/social-accounts/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/settings/social-accounts/export'
*/
exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62.url = (options?: RouteQueryOptions) => {
    return exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/settings/social-accounts/export'
*/
exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/settings/social-accounts/export'
*/
exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/settings/social-accounts/export'
*/
const exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/settings/social-accounts/export'
*/
exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/settings/social-accounts/export'
*/
exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62.form = exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62Form
/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/api/social-accounts/export'
*/
const exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd.url(options),
    method: 'get',
})

exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd.definition = {
    methods: ["get","head"],
    url: '/api/social-accounts/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/api/social-accounts/export'
*/
exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd.url = (options?: RouteQueryOptions) => {
    return exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/api/social-accounts/export'
*/
exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/api/social-accounts/export'
*/
exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/api/social-accounts/export'
*/
const exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fdForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/api/social-accounts/export'
*/
exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fdForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::exportSocialAccounts
* @see app/Http/Controllers/SettingsController.php:117
* @route '/api/social-accounts/export'
*/
exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fdForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd.form = exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fdForm

/**
* Multiple routes resolve to \App\Http\Controllers\SettingsController::exportSocialAccounts, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `exportSocialAccounts['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const exportSocialAccounts = {
    '/settings/social-accounts/export': exportSocialAccountsfb8f1619caa5bb5692b4788d2739da62,
    '/api/social-accounts/export': exportSocialAccounts5de6ab7b448b316bd8eb3296ae7f02fd,
}

/**
* @see \App\Http\Controllers\SettingsController::importSocialAccounts
* @see app/Http/Controllers/SettingsController.php:152
* @route '/settings/social-accounts/import'
*/
const importSocialAccountsac8f9a279ccf12188625480e166e7a99 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importSocialAccountsac8f9a279ccf12188625480e166e7a99.url(options),
    method: 'post',
})

importSocialAccountsac8f9a279ccf12188625480e166e7a99.definition = {
    methods: ["post"],
    url: '/settings/social-accounts/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::importSocialAccounts
* @see app/Http/Controllers/SettingsController.php:152
* @route '/settings/social-accounts/import'
*/
importSocialAccountsac8f9a279ccf12188625480e166e7a99.url = (options?: RouteQueryOptions) => {
    return importSocialAccountsac8f9a279ccf12188625480e166e7a99.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::importSocialAccounts
* @see app/Http/Controllers/SettingsController.php:152
* @route '/settings/social-accounts/import'
*/
importSocialAccountsac8f9a279ccf12188625480e166e7a99.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importSocialAccountsac8f9a279ccf12188625480e166e7a99.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::importSocialAccounts
* @see app/Http/Controllers/SettingsController.php:152
* @route '/settings/social-accounts/import'
*/
const importSocialAccountsac8f9a279ccf12188625480e166e7a99Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: importSocialAccountsac8f9a279ccf12188625480e166e7a99.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::importSocialAccounts
* @see app/Http/Controllers/SettingsController.php:152
* @route '/settings/social-accounts/import'
*/
importSocialAccountsac8f9a279ccf12188625480e166e7a99Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: importSocialAccountsac8f9a279ccf12188625480e166e7a99.url(options),
    method: 'post',
})

importSocialAccountsac8f9a279ccf12188625480e166e7a99.form = importSocialAccountsac8f9a279ccf12188625480e166e7a99Form
/**
* @see \App\Http\Controllers\SettingsController::importSocialAccounts
* @see app/Http/Controllers/SettingsController.php:152
* @route '/api/social-accounts/import'
*/
const importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3a = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3a.url(options),
    method: 'post',
})

importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3a.definition = {
    methods: ["post"],
    url: '/api/social-accounts/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::importSocialAccounts
* @see app/Http/Controllers/SettingsController.php:152
* @route '/api/social-accounts/import'
*/
importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3a.url = (options?: RouteQueryOptions) => {
    return importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3a.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::importSocialAccounts
* @see app/Http/Controllers/SettingsController.php:152
* @route '/api/social-accounts/import'
*/
importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3a.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3a.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::importSocialAccounts
* @see app/Http/Controllers/SettingsController.php:152
* @route '/api/social-accounts/import'
*/
const importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3aForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3a.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::importSocialAccounts
* @see app/Http/Controllers/SettingsController.php:152
* @route '/api/social-accounts/import'
*/
importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3aForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3a.url(options),
    method: 'post',
})

importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3a.form = importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3aForm

/**
* Multiple routes resolve to \App\Http\Controllers\SettingsController::importSocialAccounts, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `importSocialAccounts['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const importSocialAccounts = {
    '/settings/social-accounts/import': importSocialAccountsac8f9a279ccf12188625480e166e7a99,
    '/api/social-accounts/import': importSocialAccounts6b556cfcf77eefc0df1498b8ca400b3a,
}

/**
* @see \App\Http\Controllers\SettingsController::storeSocialAccount
* @see app/Http/Controllers/SettingsController.php:38
* @route '/settings/social-accounts'
*/
export const storeSocialAccount = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeSocialAccount.url(options),
    method: 'post',
})

storeSocialAccount.definition = {
    methods: ["post"],
    url: '/settings/social-accounts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::storeSocialAccount
* @see app/Http/Controllers/SettingsController.php:38
* @route '/settings/social-accounts'
*/
storeSocialAccount.url = (options?: RouteQueryOptions) => {
    return storeSocialAccount.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::storeSocialAccount
* @see app/Http/Controllers/SettingsController.php:38
* @route '/settings/social-accounts'
*/
storeSocialAccount.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeSocialAccount.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::storeSocialAccount
* @see app/Http/Controllers/SettingsController.php:38
* @route '/settings/social-accounts'
*/
const storeSocialAccountForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeSocialAccount.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::storeSocialAccount
* @see app/Http/Controllers/SettingsController.php:38
* @route '/settings/social-accounts'
*/
storeSocialAccountForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeSocialAccount.url(options),
    method: 'post',
})

storeSocialAccount.form = storeSocialAccountForm

/**
* @see \App\Http\Controllers\SettingsController::updateSocialAccount
* @see app/Http/Controllers/SettingsController.php:63
* @route '/settings/social-accounts/{id}'
*/
export const updateSocialAccount = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateSocialAccount.url(args, options),
    method: 'patch',
})

updateSocialAccount.definition = {
    methods: ["patch"],
    url: '/settings/social-accounts/{id}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\SettingsController::updateSocialAccount
* @see app/Http/Controllers/SettingsController.php:63
* @route '/settings/social-accounts/{id}'
*/
updateSocialAccount.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
    }

    return updateSocialAccount.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::updateSocialAccount
* @see app/Http/Controllers/SettingsController.php:63
* @route '/settings/social-accounts/{id}'
*/
updateSocialAccount.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateSocialAccount.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\SettingsController::updateSocialAccount
* @see app/Http/Controllers/SettingsController.php:63
* @route '/settings/social-accounts/{id}'
*/
const updateSocialAccountForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateSocialAccount.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::updateSocialAccount
* @see app/Http/Controllers/SettingsController.php:63
* @route '/settings/social-accounts/{id}'
*/
updateSocialAccountForm.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateSocialAccount.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updateSocialAccount.form = updateSocialAccountForm

/**
* @see \App\Http\Controllers\SettingsController::destroySocialAccount
* @see app/Http/Controllers/SettingsController.php:101
* @route '/settings/social-accounts/{id}'
*/
export const destroySocialAccount = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroySocialAccount.url(args, options),
    method: 'delete',
})

destroySocialAccount.definition = {
    methods: ["delete"],
    url: '/settings/social-accounts/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\SettingsController::destroySocialAccount
* @see app/Http/Controllers/SettingsController.php:101
* @route '/settings/social-accounts/{id}'
*/
destroySocialAccount.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
    }

    return destroySocialAccount.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::destroySocialAccount
* @see app/Http/Controllers/SettingsController.php:101
* @route '/settings/social-accounts/{id}'
*/
destroySocialAccount.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroySocialAccount.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\SettingsController::destroySocialAccount
* @see app/Http/Controllers/SettingsController.php:101
* @route '/settings/social-accounts/{id}'
*/
const destroySocialAccountForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroySocialAccount.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::destroySocialAccount
* @see app/Http/Controllers/SettingsController.php:101
* @route '/settings/social-accounts/{id}'
*/
destroySocialAccountForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroySocialAccount.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroySocialAccount.form = destroySocialAccountForm

/**
* @see \App\Http\Controllers\SettingsController::toggleSocialAccount
* @see app/Http/Controllers/SettingsController.php:108
* @route '/settings/social-accounts/{id}/toggle'
*/
export const toggleSocialAccount = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleSocialAccount.url(args, options),
    method: 'post',
})

toggleSocialAccount.definition = {
    methods: ["post"],
    url: '/settings/social-accounts/{id}/toggle',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::toggleSocialAccount
* @see app/Http/Controllers/SettingsController.php:108
* @route '/settings/social-accounts/{id}/toggle'
*/
toggleSocialAccount.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
    }

    return toggleSocialAccount.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::toggleSocialAccount
* @see app/Http/Controllers/SettingsController.php:108
* @route '/settings/social-accounts/{id}/toggle'
*/
toggleSocialAccount.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleSocialAccount.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::toggleSocialAccount
* @see app/Http/Controllers/SettingsController.php:108
* @route '/settings/social-accounts/{id}/toggle'
*/
const toggleSocialAccountForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggleSocialAccount.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::toggleSocialAccount
* @see app/Http/Controllers/SettingsController.php:108
* @route '/settings/social-accounts/{id}/toggle'
*/
toggleSocialAccountForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggleSocialAccount.url(args, options),
    method: 'post',
})

toggleSocialAccount.form = toggleSocialAccountForm

/**
* @see \App\Http\Controllers\SettingsController::testPostSocialAccount
* @see app/Http/Controllers/SettingsController.php:236
* @route '/settings/social-accounts/{id}/test-post'
*/
export const testPostSocialAccount = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testPostSocialAccount.url(args, options),
    method: 'post',
})

testPostSocialAccount.definition = {
    methods: ["post"],
    url: '/settings/social-accounts/{id}/test-post',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::testPostSocialAccount
* @see app/Http/Controllers/SettingsController.php:236
* @route '/settings/social-accounts/{id}/test-post'
*/
testPostSocialAccount.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
    }

    return testPostSocialAccount.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::testPostSocialAccount
* @see app/Http/Controllers/SettingsController.php:236
* @route '/settings/social-accounts/{id}/test-post'
*/
testPostSocialAccount.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testPostSocialAccount.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::testPostSocialAccount
* @see app/Http/Controllers/SettingsController.php:236
* @route '/settings/social-accounts/{id}/test-post'
*/
const testPostSocialAccountForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: testPostSocialAccount.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::testPostSocialAccount
* @see app/Http/Controllers/SettingsController.php:236
* @route '/settings/social-accounts/{id}/test-post'
*/
testPostSocialAccountForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: testPostSocialAccount.url(args, options),
    method: 'post',
})

testPostSocialAccount.form = testPostSocialAccountForm

/**
* @see \App\Http\Controllers\SettingsController::storeUser
* @see app/Http/Controllers/SettingsController.php:302
* @route '/settings/users'
*/
export const storeUser = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeUser.url(options),
    method: 'post',
})

storeUser.definition = {
    methods: ["post"],
    url: '/settings/users',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::storeUser
* @see app/Http/Controllers/SettingsController.php:302
* @route '/settings/users'
*/
storeUser.url = (options?: RouteQueryOptions) => {
    return storeUser.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::storeUser
* @see app/Http/Controllers/SettingsController.php:302
* @route '/settings/users'
*/
storeUser.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeUser.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::storeUser
* @see app/Http/Controllers/SettingsController.php:302
* @route '/settings/users'
*/
const storeUserForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeUser.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::storeUser
* @see app/Http/Controllers/SettingsController.php:302
* @route '/settings/users'
*/
storeUserForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeUser.url(options),
    method: 'post',
})

storeUser.form = storeUserForm

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:320
* @route '/settings/test-webhook'
*/
const testWebhook9ce0bb787073a659857cdf79189fb7af = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testWebhook9ce0bb787073a659857cdf79189fb7af.url(options),
    method: 'post',
})

testWebhook9ce0bb787073a659857cdf79189fb7af.definition = {
    methods: ["post"],
    url: '/settings/test-webhook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:320
* @route '/settings/test-webhook'
*/
testWebhook9ce0bb787073a659857cdf79189fb7af.url = (options?: RouteQueryOptions) => {
    return testWebhook9ce0bb787073a659857cdf79189fb7af.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:320
* @route '/settings/test-webhook'
*/
testWebhook9ce0bb787073a659857cdf79189fb7af.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testWebhook9ce0bb787073a659857cdf79189fb7af.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:320
* @route '/settings/test-webhook'
*/
const testWebhook9ce0bb787073a659857cdf79189fb7afForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: testWebhook9ce0bb787073a659857cdf79189fb7af.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:320
* @route '/settings/test-webhook'
*/
testWebhook9ce0bb787073a659857cdf79189fb7afForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: testWebhook9ce0bb787073a659857cdf79189fb7af.url(options),
    method: 'post',
})

testWebhook9ce0bb787073a659857cdf79189fb7af.form = testWebhook9ce0bb787073a659857cdf79189fb7afForm
/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:320
* @route '/api/webhooks/test'
*/
const testWebhook7086faa651d2080c96ba07426cba0cfd = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testWebhook7086faa651d2080c96ba07426cba0cfd.url(options),
    method: 'post',
})

testWebhook7086faa651d2080c96ba07426cba0cfd.definition = {
    methods: ["post"],
    url: '/api/webhooks/test',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:320
* @route '/api/webhooks/test'
*/
testWebhook7086faa651d2080c96ba07426cba0cfd.url = (options?: RouteQueryOptions) => {
    return testWebhook7086faa651d2080c96ba07426cba0cfd.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:320
* @route '/api/webhooks/test'
*/
testWebhook7086faa651d2080c96ba07426cba0cfd.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testWebhook7086faa651d2080c96ba07426cba0cfd.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:320
* @route '/api/webhooks/test'
*/
const testWebhook7086faa651d2080c96ba07426cba0cfdForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: testWebhook7086faa651d2080c96ba07426cba0cfd.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:320
* @route '/api/webhooks/test'
*/
testWebhook7086faa651d2080c96ba07426cba0cfdForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: testWebhook7086faa651d2080c96ba07426cba0cfd.url(options),
    method: 'post',
})

testWebhook7086faa651d2080c96ba07426cba0cfd.form = testWebhook7086faa651d2080c96ba07426cba0cfdForm

/**
* Multiple routes resolve to \App\Http\Controllers\SettingsController::testWebhook, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `testWebhook['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const testWebhook = {
    '/settings/test-webhook': testWebhook9ce0bb787073a659857cdf79189fb7af,
    '/api/webhooks/test': testWebhook7086faa651d2080c96ba07426cba0cfd,
}

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:351
* @route '/settings/token/exchange'
*/
const exchangeToken3da3b66a33ace2dd2478f7d90752846e = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: exchangeToken3da3b66a33ace2dd2478f7d90752846e.url(options),
    method: 'post',
})

exchangeToken3da3b66a33ace2dd2478f7d90752846e.definition = {
    methods: ["post"],
    url: '/settings/token/exchange',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:351
* @route '/settings/token/exchange'
*/
exchangeToken3da3b66a33ace2dd2478f7d90752846e.url = (options?: RouteQueryOptions) => {
    return exchangeToken3da3b66a33ace2dd2478f7d90752846e.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:351
* @route '/settings/token/exchange'
*/
exchangeToken3da3b66a33ace2dd2478f7d90752846e.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: exchangeToken3da3b66a33ace2dd2478f7d90752846e.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:351
* @route '/settings/token/exchange'
*/
const exchangeToken3da3b66a33ace2dd2478f7d90752846eForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: exchangeToken3da3b66a33ace2dd2478f7d90752846e.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:351
* @route '/settings/token/exchange'
*/
exchangeToken3da3b66a33ace2dd2478f7d90752846eForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: exchangeToken3da3b66a33ace2dd2478f7d90752846e.url(options),
    method: 'post',
})

exchangeToken3da3b66a33ace2dd2478f7d90752846e.form = exchangeToken3da3b66a33ace2dd2478f7d90752846eForm
/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:351
* @route '/api/token/exchange'
*/
const exchangeToken2c889469ba47348ef45a6881dbb6b42f = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: exchangeToken2c889469ba47348ef45a6881dbb6b42f.url(options),
    method: 'post',
})

exchangeToken2c889469ba47348ef45a6881dbb6b42f.definition = {
    methods: ["post"],
    url: '/api/token/exchange',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:351
* @route '/api/token/exchange'
*/
exchangeToken2c889469ba47348ef45a6881dbb6b42f.url = (options?: RouteQueryOptions) => {
    return exchangeToken2c889469ba47348ef45a6881dbb6b42f.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:351
* @route '/api/token/exchange'
*/
exchangeToken2c889469ba47348ef45a6881dbb6b42f.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: exchangeToken2c889469ba47348ef45a6881dbb6b42f.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:351
* @route '/api/token/exchange'
*/
const exchangeToken2c889469ba47348ef45a6881dbb6b42fForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: exchangeToken2c889469ba47348ef45a6881dbb6b42f.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:351
* @route '/api/token/exchange'
*/
exchangeToken2c889469ba47348ef45a6881dbb6b42fForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: exchangeToken2c889469ba47348ef45a6881dbb6b42f.url(options),
    method: 'post',
})

exchangeToken2c889469ba47348ef45a6881dbb6b42f.form = exchangeToken2c889469ba47348ef45a6881dbb6b42fForm

/**
* Multiple routes resolve to \App\Http\Controllers\SettingsController::exchangeToken, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `exchangeToken['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const exchangeToken = {
    '/settings/token/exchange': exchangeToken3da3b66a33ace2dd2478f7d90752846e,
    '/api/token/exchange': exchangeToken2c889469ba47348ef45a6881dbb6b42f,
}

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:464
* @route '/settings/token/verify'
*/
const verifyToken16db7258f6fca8bf3894a43c17ebe34d = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyToken16db7258f6fca8bf3894a43c17ebe34d.url(options),
    method: 'post',
})

verifyToken16db7258f6fca8bf3894a43c17ebe34d.definition = {
    methods: ["post"],
    url: '/settings/token/verify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:464
* @route '/settings/token/verify'
*/
verifyToken16db7258f6fca8bf3894a43c17ebe34d.url = (options?: RouteQueryOptions) => {
    return verifyToken16db7258f6fca8bf3894a43c17ebe34d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:464
* @route '/settings/token/verify'
*/
verifyToken16db7258f6fca8bf3894a43c17ebe34d.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyToken16db7258f6fca8bf3894a43c17ebe34d.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:464
* @route '/settings/token/verify'
*/
const verifyToken16db7258f6fca8bf3894a43c17ebe34dForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: verifyToken16db7258f6fca8bf3894a43c17ebe34d.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:464
* @route '/settings/token/verify'
*/
verifyToken16db7258f6fca8bf3894a43c17ebe34dForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: verifyToken16db7258f6fca8bf3894a43c17ebe34d.url(options),
    method: 'post',
})

verifyToken16db7258f6fca8bf3894a43c17ebe34d.form = verifyToken16db7258f6fca8bf3894a43c17ebe34dForm
/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:464
* @route '/api/token/verify'
*/
const verifyToken4bbd093b6ce3f59218745a9fd5db48bd = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: verifyToken4bbd093b6ce3f59218745a9fd5db48bd.url(options),
    method: 'get',
})

verifyToken4bbd093b6ce3f59218745a9fd5db48bd.definition = {
    methods: ["get","head"],
    url: '/api/token/verify',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:464
* @route '/api/token/verify'
*/
verifyToken4bbd093b6ce3f59218745a9fd5db48bd.url = (options?: RouteQueryOptions) => {
    return verifyToken4bbd093b6ce3f59218745a9fd5db48bd.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:464
* @route '/api/token/verify'
*/
verifyToken4bbd093b6ce3f59218745a9fd5db48bd.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: verifyToken4bbd093b6ce3f59218745a9fd5db48bd.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:464
* @route '/api/token/verify'
*/
verifyToken4bbd093b6ce3f59218745a9fd5db48bd.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: verifyToken4bbd093b6ce3f59218745a9fd5db48bd.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:464
* @route '/api/token/verify'
*/
const verifyToken4bbd093b6ce3f59218745a9fd5db48bdForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: verifyToken4bbd093b6ce3f59218745a9fd5db48bd.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:464
* @route '/api/token/verify'
*/
verifyToken4bbd093b6ce3f59218745a9fd5db48bdForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: verifyToken4bbd093b6ce3f59218745a9fd5db48bd.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:464
* @route '/api/token/verify'
*/
verifyToken4bbd093b6ce3f59218745a9fd5db48bdForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: verifyToken4bbd093b6ce3f59218745a9fd5db48bd.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

verifyToken4bbd093b6ce3f59218745a9fd5db48bd.form = verifyToken4bbd093b6ce3f59218745a9fd5db48bdForm

/**
* Multiple routes resolve to \App\Http\Controllers\SettingsController::verifyToken, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `verifyToken['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const verifyToken = {
    '/settings/token/verify': verifyToken16db7258f6fca8bf3894a43c17ebe34d,
    '/api/token/verify': verifyToken4bbd093b6ce3f59218745a9fd5db48bd,
}

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:532
* @route '/settings/suggest-hashtags'
*/
const suggestHashtagsd021a4a4da2083878bc31b9348b50dce = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suggestHashtagsd021a4a4da2083878bc31b9348b50dce.url(options),
    method: 'post',
})

suggestHashtagsd021a4a4da2083878bc31b9348b50dce.definition = {
    methods: ["post"],
    url: '/settings/suggest-hashtags',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:532
* @route '/settings/suggest-hashtags'
*/
suggestHashtagsd021a4a4da2083878bc31b9348b50dce.url = (options?: RouteQueryOptions) => {
    return suggestHashtagsd021a4a4da2083878bc31b9348b50dce.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:532
* @route '/settings/suggest-hashtags'
*/
suggestHashtagsd021a4a4da2083878bc31b9348b50dce.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suggestHashtagsd021a4a4da2083878bc31b9348b50dce.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:532
* @route '/settings/suggest-hashtags'
*/
const suggestHashtagsd021a4a4da2083878bc31b9348b50dceForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: suggestHashtagsd021a4a4da2083878bc31b9348b50dce.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:532
* @route '/settings/suggest-hashtags'
*/
suggestHashtagsd021a4a4da2083878bc31b9348b50dceForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: suggestHashtagsd021a4a4da2083878bc31b9348b50dce.url(options),
    method: 'post',
})

suggestHashtagsd021a4a4da2083878bc31b9348b50dce.form = suggestHashtagsd021a4a4da2083878bc31b9348b50dceForm
/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:532
* @route '/api/integrations/suggest-hashtags'
*/
const suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031.url(options),
    method: 'post',
})

suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031.definition = {
    methods: ["post"],
    url: '/api/integrations/suggest-hashtags',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:532
* @route '/api/integrations/suggest-hashtags'
*/
suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031.url = (options?: RouteQueryOptions) => {
    return suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:532
* @route '/api/integrations/suggest-hashtags'
*/
suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:532
* @route '/api/integrations/suggest-hashtags'
*/
const suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:532
* @route '/api/integrations/suggest-hashtags'
*/
suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031.url(options),
    method: 'post',
})

suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031.form = suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031Form

/**
* Multiple routes resolve to \App\Http\Controllers\SettingsController::suggestHashtags, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `suggestHashtags['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const suggestHashtags = {
    '/settings/suggest-hashtags': suggestHashtagsd021a4a4da2083878bc31b9348b50dce,
    '/api/integrations/suggest-hashtags': suggestHashtagsa30b4477e066ae6c9cb0a49bf9b54031,
}

const SettingsController = { index, update, exportSocialAccounts, importSocialAccounts, storeSocialAccount, updateSocialAccount, destroySocialAccount, toggleSocialAccount, testPostSocialAccount, storeUser, testWebhook, exchangeToken, verifyToken, suggestHashtags }

export default SettingsController