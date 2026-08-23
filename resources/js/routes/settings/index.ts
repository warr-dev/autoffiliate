import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import app7ce4aa from './app'
import social from './social'
import users from './users'
import webhook from './webhook'
import token from './token'
import hashtags from './hashtags'
/**
* @see \App\Http\Controllers\SettingsController::app
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
export const app = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: app.url(options),
    method: 'get',
})

app.definition = {
    methods: ["get","head"],
    url: '/settings/app',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\SettingsController::app
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
app.url = (options?: RouteQueryOptions) => {
    return app.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::app
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
app.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: app.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::app
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
app.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: app.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\SettingsController::app
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
const appForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: app.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::app
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
appForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: app.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\SettingsController::app
* @see app/Http/Controllers/SettingsController.php:18
* @route '/settings/app'
*/
appForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: app.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

app.form = appForm

const settings = {
    app: Object.assign(app, app7ce4aa),
    social: Object.assign(social, social),
    users: Object.assign(users, users),
    webhook: Object.assign(webhook, webhook),
    token: Object.assign(token, token),
    hashtags: Object.assign(hashtags, hashtags),
}

export default settings