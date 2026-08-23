import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:16
* @route '/dashboard'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:16
* @route '/dashboard'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:16
* @route '/dashboard'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:16
* @route '/dashboard'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:16
* @route '/dashboard'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:16
* @route '/dashboard'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::index
* @see app/Http/Controllers/DashboardController.php:16
* @route '/dashboard'
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
* @see \App\Http\Controllers\DashboardController::aiAnalytics
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
export const aiAnalytics = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: aiAnalytics.url(options),
    method: 'get',
})

aiAnalytics.definition = {
    methods: ["get","head"],
    url: '/api/analytics/ai',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::aiAnalytics
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
aiAnalytics.url = (options?: RouteQueryOptions) => {
    return aiAnalytics.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::aiAnalytics
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
aiAnalytics.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: aiAnalytics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::aiAnalytics
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
aiAnalytics.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: aiAnalytics.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::aiAnalytics
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
const aiAnalyticsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: aiAnalytics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::aiAnalytics
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
aiAnalyticsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: aiAnalytics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::aiAnalytics
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
aiAnalyticsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: aiAnalytics.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

aiAnalytics.form = aiAnalyticsForm

const DashboardController = { index, aiAnalytics }

export default DashboardController