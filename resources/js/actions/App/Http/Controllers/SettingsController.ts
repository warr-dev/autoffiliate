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
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/settings/app',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/settings/app'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/settings/app'
*/
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/settings/app'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::update
* @see app/Http/Controllers/SettingsController.php:27
* @route '/settings/app'
*/
updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(options),
    method: 'post',
})

update.form = updateForm

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
* @see app/Http/Controllers/SettingsController.php:117
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
* @see app/Http/Controllers/SettingsController.php:117
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
* @see app/Http/Controllers/SettingsController.php:117
* @route '/settings/social-accounts/{id}/test-post'
*/
testPostSocialAccount.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testPostSocialAccount.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::testPostSocialAccount
* @see app/Http/Controllers/SettingsController.php:117
* @route '/settings/social-accounts/{id}/test-post'
*/
const testPostSocialAccountForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: testPostSocialAccount.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::testPostSocialAccount
* @see app/Http/Controllers/SettingsController.php:117
* @route '/settings/social-accounts/{id}/test-post'
*/
testPostSocialAccountForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: testPostSocialAccount.url(args, options),
    method: 'post',
})

testPostSocialAccount.form = testPostSocialAccountForm

/**
* @see \App\Http\Controllers\SettingsController::storeUser
* @see app/Http/Controllers/SettingsController.php:183
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
* @see app/Http/Controllers/SettingsController.php:183
* @route '/settings/users'
*/
storeUser.url = (options?: RouteQueryOptions) => {
    return storeUser.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::storeUser
* @see app/Http/Controllers/SettingsController.php:183
* @route '/settings/users'
*/
storeUser.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeUser.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::storeUser
* @see app/Http/Controllers/SettingsController.php:183
* @route '/settings/users'
*/
const storeUserForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeUser.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::storeUser
* @see app/Http/Controllers/SettingsController.php:183
* @route '/settings/users'
*/
storeUserForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeUser.url(options),
    method: 'post',
})

storeUser.form = storeUserForm

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:201
* @route '/settings/test-webhook'
*/
export const testWebhook = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testWebhook.url(options),
    method: 'post',
})

testWebhook.definition = {
    methods: ["post"],
    url: '/settings/test-webhook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:201
* @route '/settings/test-webhook'
*/
testWebhook.url = (options?: RouteQueryOptions) => {
    return testWebhook.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:201
* @route '/settings/test-webhook'
*/
testWebhook.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: testWebhook.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:201
* @route '/settings/test-webhook'
*/
const testWebhookForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: testWebhook.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::testWebhook
* @see app/Http/Controllers/SettingsController.php:201
* @route '/settings/test-webhook'
*/
testWebhookForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: testWebhook.url(options),
    method: 'post',
})

testWebhook.form = testWebhookForm

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:232
* @route '/settings/token/exchange'
*/
export const exchangeToken = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: exchangeToken.url(options),
    method: 'post',
})

exchangeToken.definition = {
    methods: ["post"],
    url: '/settings/token/exchange',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:232
* @route '/settings/token/exchange'
*/
exchangeToken.url = (options?: RouteQueryOptions) => {
    return exchangeToken.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:232
* @route '/settings/token/exchange'
*/
exchangeToken.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: exchangeToken.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:232
* @route '/settings/token/exchange'
*/
const exchangeTokenForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: exchangeToken.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::exchangeToken
* @see app/Http/Controllers/SettingsController.php:232
* @route '/settings/token/exchange'
*/
exchangeTokenForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: exchangeToken.url(options),
    method: 'post',
})

exchangeToken.form = exchangeTokenForm

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:345
* @route '/settings/token/verify'
*/
export const verifyToken = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyToken.url(options),
    method: 'post',
})

verifyToken.definition = {
    methods: ["post"],
    url: '/settings/token/verify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:345
* @route '/settings/token/verify'
*/
verifyToken.url = (options?: RouteQueryOptions) => {
    return verifyToken.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:345
* @route '/settings/token/verify'
*/
verifyToken.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyToken.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:345
* @route '/settings/token/verify'
*/
const verifyTokenForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: verifyToken.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::verifyToken
* @see app/Http/Controllers/SettingsController.php:345
* @route '/settings/token/verify'
*/
verifyTokenForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: verifyToken.url(options),
    method: 'post',
})

verifyToken.form = verifyTokenForm

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:413
* @route '/settings/suggest-hashtags'
*/
export const suggestHashtags = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suggestHashtags.url(options),
    method: 'post',
})

suggestHashtags.definition = {
    methods: ["post"],
    url: '/settings/suggest-hashtags',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:413
* @route '/settings/suggest-hashtags'
*/
suggestHashtags.url = (options?: RouteQueryOptions) => {
    return suggestHashtags.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:413
* @route '/settings/suggest-hashtags'
*/
suggestHashtags.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suggestHashtags.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:413
* @route '/settings/suggest-hashtags'
*/
const suggestHashtagsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: suggestHashtags.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::suggestHashtags
* @see app/Http/Controllers/SettingsController.php:413
* @route '/settings/suggest-hashtags'
*/
suggestHashtagsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: suggestHashtags.url(options),
    method: 'post',
})

suggestHashtags.form = suggestHashtagsForm

const SettingsController = { index, update, storeSocialAccount, updateSocialAccount, destroySocialAccount, toggleSocialAccount, testPostSocialAccount, storeUser, testWebhook, exchangeToken, verifyToken, suggestHashtags }

export default SettingsController