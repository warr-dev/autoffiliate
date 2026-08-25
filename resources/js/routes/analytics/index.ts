import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see app/Http/Controllers/AnalyticsController.php:19
* @route '/analytics'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/analytics',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see app/Http/Controllers/AnalyticsController.php:19
* @route '/analytics'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see app/Http/Controllers/AnalyticsController.php:19
* @route '/analytics'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see app/Http/Controllers/AnalyticsController.php:19
* @route '/analytics'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see app/Http/Controllers/AnalyticsController.php:19
* @route '/analytics'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see app/Http/Controllers/AnalyticsController.php:19
* @route '/analytics'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see app/Http/Controllers/AnalyticsController.php:19
* @route '/analytics'
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
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see app/Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/analytics/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see app/Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see app/Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see app/Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see app/Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see app/Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see app/Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
exportMethodForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

exportMethod.form = exportMethodForm

const analytics = {
    index: Object.assign(index, index),
    export: Object.assign(exportMethod, exportMethod),
}

export default analytics