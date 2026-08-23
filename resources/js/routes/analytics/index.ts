import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\DashboardController::ai
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
export const ai = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ai.url(options),
    method: 'get',
})

ai.definition = {
    methods: ["get","head"],
    url: '/api/analytics/ai',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\DashboardController::ai
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
ai.url = (options?: RouteQueryOptions) => {
    return ai.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\DashboardController::ai
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
ai.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ai.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::ai
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
ai.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ai.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\DashboardController::ai
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
const aiForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ai.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::ai
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
aiForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ai.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\DashboardController::ai
* @see app/Http/Controllers/DashboardController.php:27
* @route '/api/analytics/ai'
*/
aiForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: ai.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

ai.form = aiForm

const analytics = {
    ai: Object.assign(ai, ai),
}

export default analytics