import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\SettingsController::suggest
* @see app/Http/Controllers/SettingsController.php:413
* @route '/settings/suggest-hashtags'
*/
export const suggest = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suggest.url(options),
    method: 'post',
})

suggest.definition = {
    methods: ["post"],
    url: '/settings/suggest-hashtags',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::suggest
* @see app/Http/Controllers/SettingsController.php:413
* @route '/settings/suggest-hashtags'
*/
suggest.url = (options?: RouteQueryOptions) => {
    return suggest.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::suggest
* @see app/Http/Controllers/SettingsController.php:413
* @route '/settings/suggest-hashtags'
*/
suggest.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: suggest.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::suggest
* @see app/Http/Controllers/SettingsController.php:413
* @route '/settings/suggest-hashtags'
*/
const suggestForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: suggest.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::suggest
* @see app/Http/Controllers/SettingsController.php:413
* @route '/settings/suggest-hashtags'
*/
suggestForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: suggest.url(options),
    method: 'post',
})

suggest.form = suggestForm

const hashtags = {
    suggest: Object.assign(suggest, suggest),
}

export default hashtags