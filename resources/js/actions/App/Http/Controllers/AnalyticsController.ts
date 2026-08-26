import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see Http/Controllers/AnalyticsController.php:19
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
* @see Http/Controllers/AnalyticsController.php:19
* @route '/analytics'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see Http/Controllers/AnalyticsController.php:19
* @route '/analytics'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see Http/Controllers/AnalyticsController.php:19
* @route '/analytics'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see Http/Controllers/AnalyticsController.php:19
* @route '/analytics'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see Http/Controllers/AnalyticsController.php:19
* @route '/analytics'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::index
* @see Http/Controllers/AnalyticsController.php:19
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
* @see Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
const exportMethoda17848a5597410ddb253ce53593ad27a = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethoda17848a5597410ddb253ce53593ad27a.url(options),
    method: 'get',
})

exportMethoda17848a5597410ddb253ce53593ad27a.definition = {
    methods: ["get","head"],
    url: '/analytics/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
exportMethoda17848a5597410ddb253ce53593ad27a.url = (options?: RouteQueryOptions) => {
    return exportMethoda17848a5597410ddb253ce53593ad27a.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
exportMethoda17848a5597410ddb253ce53593ad27a.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethoda17848a5597410ddb253ce53593ad27a.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
exportMethoda17848a5597410ddb253ce53593ad27a.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethoda17848a5597410ddb253ce53593ad27a.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
const exportMethoda17848a5597410ddb253ce53593ad27aForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethoda17848a5597410ddb253ce53593ad27a.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
exportMethoda17848a5597410ddb253ce53593ad27aForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethoda17848a5597410ddb253ce53593ad27a.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/analytics/export'
*/
exportMethoda17848a5597410ddb253ce53593ad27aForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethoda17848a5597410ddb253ce53593ad27a.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

exportMethoda17848a5597410ddb253ce53593ad27a.form = exportMethoda17848a5597410ddb253ce53593ad27aForm
/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/api/analytics/ai/export'
*/
const exportMethod0c52289e4c65daeebf19cd785126fba3 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod0c52289e4c65daeebf19cd785126fba3.url(options),
    method: 'get',
})

exportMethod0c52289e4c65daeebf19cd785126fba3.definition = {
    methods: ["get","head"],
    url: '/api/analytics/ai/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/api/analytics/ai/export'
*/
exportMethod0c52289e4c65daeebf19cd785126fba3.url = (options?: RouteQueryOptions) => {
    return exportMethod0c52289e4c65daeebf19cd785126fba3.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/api/analytics/ai/export'
*/
exportMethod0c52289e4c65daeebf19cd785126fba3.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod0c52289e4c65daeebf19cd785126fba3.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/api/analytics/ai/export'
*/
exportMethod0c52289e4c65daeebf19cd785126fba3.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod0c52289e4c65daeebf19cd785126fba3.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/api/analytics/ai/export'
*/
const exportMethod0c52289e4c65daeebf19cd785126fba3Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod0c52289e4c65daeebf19cd785126fba3.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/api/analytics/ai/export'
*/
exportMethod0c52289e4c65daeebf19cd785126fba3Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod0c52289e4c65daeebf19cd785126fba3.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::exportMethod
* @see Http/Controllers/AnalyticsController.php:56
* @route '/api/analytics/ai/export'
*/
exportMethod0c52289e4c65daeebf19cd785126fba3Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod0c52289e4c65daeebf19cd785126fba3.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

exportMethod0c52289e4c65daeebf19cd785126fba3.form = exportMethod0c52289e4c65daeebf19cd785126fba3Form

/**
* Multiple routes resolve to \App\Http\Controllers\AnalyticsController::export, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `exportMethod['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const exportMethod = {
    '/analytics/export': exportMethoda17848a5597410ddb253ce53593ad27a,
    '/api/analytics/ai/export': exportMethod0c52289e4c65daeebf19cd785126fba3,
}

/**
* @see \App\Http\Controllers\AnalyticsController::apiAnalytics
* @see Http/Controllers/AnalyticsController.php:41
* @route '/api/analytics/ai'
*/
export const apiAnalytics = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: apiAnalytics.url(options),
    method: 'get',
})

apiAnalytics.definition = {
    methods: ["get","head"],
    url: '/api/analytics/ai',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\AnalyticsController::apiAnalytics
* @see Http/Controllers/AnalyticsController.php:41
* @route '/api/analytics/ai'
*/
apiAnalytics.url = (options?: RouteQueryOptions) => {
    return apiAnalytics.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AnalyticsController::apiAnalytics
* @see Http/Controllers/AnalyticsController.php:41
* @route '/api/analytics/ai'
*/
apiAnalytics.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: apiAnalytics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::apiAnalytics
* @see Http/Controllers/AnalyticsController.php:41
* @route '/api/analytics/ai'
*/
apiAnalytics.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: apiAnalytics.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\AnalyticsController::apiAnalytics
* @see Http/Controllers/AnalyticsController.php:41
* @route '/api/analytics/ai'
*/
const apiAnalyticsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: apiAnalytics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::apiAnalytics
* @see Http/Controllers/AnalyticsController.php:41
* @route '/api/analytics/ai'
*/
apiAnalyticsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: apiAnalytics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\AnalyticsController::apiAnalytics
* @see Http/Controllers/AnalyticsController.php:41
* @route '/api/analytics/ai'
*/
apiAnalyticsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: apiAnalytics.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

apiAnalytics.form = apiAnalyticsForm

/**
* @see \App\Http\Controllers\AnalyticsController::clear
* @see Http/Controllers/AnalyticsController.php:165
* @route '/api/analytics/ai/clear'
*/
export const clear = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clear.url(options),
    method: 'post',
})

clear.definition = {
    methods: ["post"],
    url: '/api/analytics/ai/clear',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\AnalyticsController::clear
* @see Http/Controllers/AnalyticsController.php:165
* @route '/api/analytics/ai/clear'
*/
clear.url = (options?: RouteQueryOptions) => {
    return clear.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\AnalyticsController::clear
* @see Http/Controllers/AnalyticsController.php:165
* @route '/api/analytics/ai/clear'
*/
clear.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clear.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AnalyticsController::clear
* @see Http/Controllers/AnalyticsController.php:165
* @route '/api/analytics/ai/clear'
*/
const clearForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: clear.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\AnalyticsController::clear
* @see Http/Controllers/AnalyticsController.php:165
* @route '/api/analytics/ai/clear'
*/
clearForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: clear.url(options),
    method: 'post',
})

clear.form = clearForm

const AnalyticsController = { index, exportMethod, apiAnalytics, clear, export: exportMethod }

export default AnalyticsController