import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\WorkflowController::index
* @see app/Http/Controllers/WorkflowController.php:19
* @route '/automated'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/automated',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WorkflowController::index
* @see app/Http/Controllers/WorkflowController.php:19
* @route '/automated'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::index
* @see app/Http/Controllers/WorkflowController.php:19
* @route '/automated'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WorkflowController::index
* @see app/Http/Controllers/WorkflowController.php:19
* @route '/automated'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\WorkflowController::index
* @see app/Http/Controllers/WorkflowController.php:19
* @route '/automated'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WorkflowController::index
* @see app/Http/Controllers/WorkflowController.php:19
* @route '/automated'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WorkflowController::index
* @see app/Http/Controllers/WorkflowController.php:19
* @route '/automated'
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
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/automated/export'
*/
const exportMethod197160f9f4bff9afdb7599c0b7b5d91d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod197160f9f4bff9afdb7599c0b7b5d91d.url(options),
    method: 'get',
})

exportMethod197160f9f4bff9afdb7599c0b7b5d91d.definition = {
    methods: ["get","head"],
    url: '/automated/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/automated/export'
*/
exportMethod197160f9f4bff9afdb7599c0b7b5d91d.url = (options?: RouteQueryOptions) => {
    return exportMethod197160f9f4bff9afdb7599c0b7b5d91d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/automated/export'
*/
exportMethod197160f9f4bff9afdb7599c0b7b5d91d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod197160f9f4bff9afdb7599c0b7b5d91d.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/automated/export'
*/
exportMethod197160f9f4bff9afdb7599c0b7b5d91d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod197160f9f4bff9afdb7599c0b7b5d91d.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/automated/export'
*/
const exportMethod197160f9f4bff9afdb7599c0b7b5d91dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod197160f9f4bff9afdb7599c0b7b5d91d.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/automated/export'
*/
exportMethod197160f9f4bff9afdb7599c0b7b5d91dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod197160f9f4bff9afdb7599c0b7b5d91d.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/automated/export'
*/
exportMethod197160f9f4bff9afdb7599c0b7b5d91dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethod197160f9f4bff9afdb7599c0b7b5d91d.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

exportMethod197160f9f4bff9afdb7599c0b7b5d91d.form = exportMethod197160f9f4bff9afdb7599c0b7b5d91dForm
/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/api/workflows/export'
*/
const exportMethoded033160fa6801c6ed0ea2d1e13b96fe = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethoded033160fa6801c6ed0ea2d1e13b96fe.url(options),
    method: 'get',
})

exportMethoded033160fa6801c6ed0ea2d1e13b96fe.definition = {
    methods: ["get","head"],
    url: '/api/workflows/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/api/workflows/export'
*/
exportMethoded033160fa6801c6ed0ea2d1e13b96fe.url = (options?: RouteQueryOptions) => {
    return exportMethoded033160fa6801c6ed0ea2d1e13b96fe.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/api/workflows/export'
*/
exportMethoded033160fa6801c6ed0ea2d1e13b96fe.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethoded033160fa6801c6ed0ea2d1e13b96fe.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/api/workflows/export'
*/
exportMethoded033160fa6801c6ed0ea2d1e13b96fe.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethoded033160fa6801c6ed0ea2d1e13b96fe.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/api/workflows/export'
*/
const exportMethoded033160fa6801c6ed0ea2d1e13b96feForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethoded033160fa6801c6ed0ea2d1e13b96fe.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/api/workflows/export'
*/
exportMethoded033160fa6801c6ed0ea2d1e13b96feForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethoded033160fa6801c6ed0ea2d1e13b96fe.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\WorkflowController::exportMethod
* @see app/Http/Controllers/WorkflowController.php:306
* @route '/api/workflows/export'
*/
exportMethoded033160fa6801c6ed0ea2d1e13b96feForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: exportMethoded033160fa6801c6ed0ea2d1e13b96fe.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

exportMethoded033160fa6801c6ed0ea2d1e13b96fe.form = exportMethoded033160fa6801c6ed0ea2d1e13b96feForm

/**
* Multiple routes resolve to \App\Http\Controllers\WorkflowController::export, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `exportMethod['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const exportMethod = {
    '/automated/export': exportMethod197160f9f4bff9afdb7599c0b7b5d91d,
    '/api/workflows/export': exportMethoded033160fa6801c6ed0ea2d1e13b96fe,
}

/**
* @see \App\Http\Controllers\WorkflowController::importMethod
* @see app/Http/Controllers/WorkflowController.php:350
* @route '/automated/import'
*/
const importMethod330408233ea526a475d296bade6283da = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod330408233ea526a475d296bade6283da.url(options),
    method: 'post',
})

importMethod330408233ea526a475d296bade6283da.definition = {
    methods: ["post"],
    url: '/automated/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\WorkflowController::importMethod
* @see app/Http/Controllers/WorkflowController.php:350
* @route '/automated/import'
*/
importMethod330408233ea526a475d296bade6283da.url = (options?: RouteQueryOptions) => {
    return importMethod330408233ea526a475d296bade6283da.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::importMethod
* @see app/Http/Controllers/WorkflowController.php:350
* @route '/automated/import'
*/
importMethod330408233ea526a475d296bade6283da.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod330408233ea526a475d296bade6283da.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::importMethod
* @see app/Http/Controllers/WorkflowController.php:350
* @route '/automated/import'
*/
const importMethod330408233ea526a475d296bade6283daForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: importMethod330408233ea526a475d296bade6283da.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::importMethod
* @see app/Http/Controllers/WorkflowController.php:350
* @route '/automated/import'
*/
importMethod330408233ea526a475d296bade6283daForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: importMethod330408233ea526a475d296bade6283da.url(options),
    method: 'post',
})

importMethod330408233ea526a475d296bade6283da.form = importMethod330408233ea526a475d296bade6283daForm
/**
* @see \App\Http\Controllers\WorkflowController::importMethod
* @see app/Http/Controllers/WorkflowController.php:350
* @route '/api/workflows/import'
*/
const importMethod379325e14642f8461cbd5d5e7e63296c = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod379325e14642f8461cbd5d5e7e63296c.url(options),
    method: 'post',
})

importMethod379325e14642f8461cbd5d5e7e63296c.definition = {
    methods: ["post"],
    url: '/api/workflows/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\WorkflowController::importMethod
* @see app/Http/Controllers/WorkflowController.php:350
* @route '/api/workflows/import'
*/
importMethod379325e14642f8461cbd5d5e7e63296c.url = (options?: RouteQueryOptions) => {
    return importMethod379325e14642f8461cbd5d5e7e63296c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::importMethod
* @see app/Http/Controllers/WorkflowController.php:350
* @route '/api/workflows/import'
*/
importMethod379325e14642f8461cbd5d5e7e63296c.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod379325e14642f8461cbd5d5e7e63296c.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::importMethod
* @see app/Http/Controllers/WorkflowController.php:350
* @route '/api/workflows/import'
*/
const importMethod379325e14642f8461cbd5d5e7e63296cForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: importMethod379325e14642f8461cbd5d5e7e63296c.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::importMethod
* @see app/Http/Controllers/WorkflowController.php:350
* @route '/api/workflows/import'
*/
importMethod379325e14642f8461cbd5d5e7e63296cForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: importMethod379325e14642f8461cbd5d5e7e63296c.url(options),
    method: 'post',
})

importMethod379325e14642f8461cbd5d5e7e63296c.form = importMethod379325e14642f8461cbd5d5e7e63296cForm

/**
* Multiple routes resolve to \App\Http\Controllers\WorkflowController::import, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `importMethod['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const importMethod = {
    '/automated/import': importMethod330408233ea526a475d296bade6283da,
    '/api/workflows/import': importMethod379325e14642f8461cbd5d5e7e63296c,
}

/**
* @see \App\Http\Controllers\WorkflowController::store
* @see app/Http/Controllers/WorkflowController.php:61
* @route '/automated'
*/
const store0ec9c4e992701cfe8f107223c11bc108 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store0ec9c4e992701cfe8f107223c11bc108.url(options),
    method: 'post',
})

store0ec9c4e992701cfe8f107223c11bc108.definition = {
    methods: ["post"],
    url: '/automated',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\WorkflowController::store
* @see app/Http/Controllers/WorkflowController.php:61
* @route '/automated'
*/
store0ec9c4e992701cfe8f107223c11bc108.url = (options?: RouteQueryOptions) => {
    return store0ec9c4e992701cfe8f107223c11bc108.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::store
* @see app/Http/Controllers/WorkflowController.php:61
* @route '/automated'
*/
store0ec9c4e992701cfe8f107223c11bc108.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store0ec9c4e992701cfe8f107223c11bc108.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::store
* @see app/Http/Controllers/WorkflowController.php:61
* @route '/automated'
*/
const store0ec9c4e992701cfe8f107223c11bc108Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store0ec9c4e992701cfe8f107223c11bc108.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::store
* @see app/Http/Controllers/WorkflowController.php:61
* @route '/automated'
*/
store0ec9c4e992701cfe8f107223c11bc108Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store0ec9c4e992701cfe8f107223c11bc108.url(options),
    method: 'post',
})

store0ec9c4e992701cfe8f107223c11bc108.form = store0ec9c4e992701cfe8f107223c11bc108Form
/**
* @see \App\Http\Controllers\WorkflowController::store
* @see app/Http/Controllers/WorkflowController.php:61
* @route '/api/workflows/rules'
*/
const store2f2a0c62402f67f7b337c3ece16d90cc = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store2f2a0c62402f67f7b337c3ece16d90cc.url(options),
    method: 'post',
})

store2f2a0c62402f67f7b337c3ece16d90cc.definition = {
    methods: ["post"],
    url: '/api/workflows/rules',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\WorkflowController::store
* @see app/Http/Controllers/WorkflowController.php:61
* @route '/api/workflows/rules'
*/
store2f2a0c62402f67f7b337c3ece16d90cc.url = (options?: RouteQueryOptions) => {
    return store2f2a0c62402f67f7b337c3ece16d90cc.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::store
* @see app/Http/Controllers/WorkflowController.php:61
* @route '/api/workflows/rules'
*/
store2f2a0c62402f67f7b337c3ece16d90cc.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store2f2a0c62402f67f7b337c3ece16d90cc.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::store
* @see app/Http/Controllers/WorkflowController.php:61
* @route '/api/workflows/rules'
*/
const store2f2a0c62402f67f7b337c3ece16d90ccForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store2f2a0c62402f67f7b337c3ece16d90cc.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::store
* @see app/Http/Controllers/WorkflowController.php:61
* @route '/api/workflows/rules'
*/
store2f2a0c62402f67f7b337c3ece16d90ccForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store2f2a0c62402f67f7b337c3ece16d90cc.url(options),
    method: 'post',
})

store2f2a0c62402f67f7b337c3ece16d90cc.form = store2f2a0c62402f67f7b337c3ece16d90ccForm

/**
* Multiple routes resolve to \App\Http\Controllers\WorkflowController::store, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `store['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const store = {
    '/automated': store0ec9c4e992701cfe8f107223c11bc108,
    '/api/workflows/rules': store2f2a0c62402f67f7b337c3ece16d90cc,
}

/**
* @see \App\Http\Controllers\WorkflowController::execute
* @see app/Http/Controllers/WorkflowController.php:138
* @route '/automated/execute'
*/
const executef616d2f67c2aca48bea13c99d26d8144 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: executef616d2f67c2aca48bea13c99d26d8144.url(options),
    method: 'post',
})

executef616d2f67c2aca48bea13c99d26d8144.definition = {
    methods: ["post"],
    url: '/automated/execute',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\WorkflowController::execute
* @see app/Http/Controllers/WorkflowController.php:138
* @route '/automated/execute'
*/
executef616d2f67c2aca48bea13c99d26d8144.url = (options?: RouteQueryOptions) => {
    return executef616d2f67c2aca48bea13c99d26d8144.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::execute
* @see app/Http/Controllers/WorkflowController.php:138
* @route '/automated/execute'
*/
executef616d2f67c2aca48bea13c99d26d8144.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: executef616d2f67c2aca48bea13c99d26d8144.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::execute
* @see app/Http/Controllers/WorkflowController.php:138
* @route '/automated/execute'
*/
const executef616d2f67c2aca48bea13c99d26d8144Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: executef616d2f67c2aca48bea13c99d26d8144.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::execute
* @see app/Http/Controllers/WorkflowController.php:138
* @route '/automated/execute'
*/
executef616d2f67c2aca48bea13c99d26d8144Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: executef616d2f67c2aca48bea13c99d26d8144.url(options),
    method: 'post',
})

executef616d2f67c2aca48bea13c99d26d8144.form = executef616d2f67c2aca48bea13c99d26d8144Form
/**
* @see \App\Http\Controllers\WorkflowController::execute
* @see app/Http/Controllers/WorkflowController.php:138
* @route '/api/workflows/execute'
*/
const execute17e2555789255b87bcca6c09b17de1bb = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: execute17e2555789255b87bcca6c09b17de1bb.url(options),
    method: 'post',
})

execute17e2555789255b87bcca6c09b17de1bb.definition = {
    methods: ["post"],
    url: '/api/workflows/execute',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\WorkflowController::execute
* @see app/Http/Controllers/WorkflowController.php:138
* @route '/api/workflows/execute'
*/
execute17e2555789255b87bcca6c09b17de1bb.url = (options?: RouteQueryOptions) => {
    return execute17e2555789255b87bcca6c09b17de1bb.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::execute
* @see app/Http/Controllers/WorkflowController.php:138
* @route '/api/workflows/execute'
*/
execute17e2555789255b87bcca6c09b17de1bb.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: execute17e2555789255b87bcca6c09b17de1bb.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::execute
* @see app/Http/Controllers/WorkflowController.php:138
* @route '/api/workflows/execute'
*/
const execute17e2555789255b87bcca6c09b17de1bbForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: execute17e2555789255b87bcca6c09b17de1bb.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::execute
* @see app/Http/Controllers/WorkflowController.php:138
* @route '/api/workflows/execute'
*/
execute17e2555789255b87bcca6c09b17de1bbForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: execute17e2555789255b87bcca6c09b17de1bb.url(options),
    method: 'post',
})

execute17e2555789255b87bcca6c09b17de1bb.form = execute17e2555789255b87bcca6c09b17de1bbForm

/**
* Multiple routes resolve to \App\Http\Controllers\WorkflowController::execute, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `execute['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const execute = {
    '/automated/execute': executef616d2f67c2aca48bea13c99d26d8144,
    '/api/workflows/execute': execute17e2555789255b87bcca6c09b17de1bb,
}

/**
* @see \App\Http\Controllers\WorkflowController::toggleStatus
* @see app/Http/Controllers/WorkflowController.php:125
* @route '/automated/{id}/toggle'
*/
const toggleStatusc72446fb39b1b3884ca40b8581d80463 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleStatusc72446fb39b1b3884ca40b8581d80463.url(args, options),
    method: 'post',
})

toggleStatusc72446fb39b1b3884ca40b8581d80463.definition = {
    methods: ["post"],
    url: '/automated/{id}/toggle',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\WorkflowController::toggleStatus
* @see app/Http/Controllers/WorkflowController.php:125
* @route '/automated/{id}/toggle'
*/
toggleStatusc72446fb39b1b3884ca40b8581d80463.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return toggleStatusc72446fb39b1b3884ca40b8581d80463.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::toggleStatus
* @see app/Http/Controllers/WorkflowController.php:125
* @route '/automated/{id}/toggle'
*/
toggleStatusc72446fb39b1b3884ca40b8581d80463.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleStatusc72446fb39b1b3884ca40b8581d80463.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::toggleStatus
* @see app/Http/Controllers/WorkflowController.php:125
* @route '/automated/{id}/toggle'
*/
const toggleStatusc72446fb39b1b3884ca40b8581d80463Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggleStatusc72446fb39b1b3884ca40b8581d80463.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::toggleStatus
* @see app/Http/Controllers/WorkflowController.php:125
* @route '/automated/{id}/toggle'
*/
toggleStatusc72446fb39b1b3884ca40b8581d80463Form.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggleStatusc72446fb39b1b3884ca40b8581d80463.url(args, options),
    method: 'post',
})

toggleStatusc72446fb39b1b3884ca40b8581d80463.form = toggleStatusc72446fb39b1b3884ca40b8581d80463Form
/**
* @see \App\Http\Controllers\WorkflowController::toggleStatus
* @see app/Http/Controllers/WorkflowController.php:125
* @route '/api/workflows/rules/{id}/status'
*/
const toggleStatus000b49339a52f8090087438a59516318 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: toggleStatus000b49339a52f8090087438a59516318.url(args, options),
    method: 'put',
})

toggleStatus000b49339a52f8090087438a59516318.definition = {
    methods: ["put"],
    url: '/api/workflows/rules/{id}/status',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\WorkflowController::toggleStatus
* @see app/Http/Controllers/WorkflowController.php:125
* @route '/api/workflows/rules/{id}/status'
*/
toggleStatus000b49339a52f8090087438a59516318.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return toggleStatus000b49339a52f8090087438a59516318.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::toggleStatus
* @see app/Http/Controllers/WorkflowController.php:125
* @route '/api/workflows/rules/{id}/status'
*/
toggleStatus000b49339a52f8090087438a59516318.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: toggleStatus000b49339a52f8090087438a59516318.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\WorkflowController::toggleStatus
* @see app/Http/Controllers/WorkflowController.php:125
* @route '/api/workflows/rules/{id}/status'
*/
const toggleStatus000b49339a52f8090087438a59516318Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggleStatus000b49339a52f8090087438a59516318.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::toggleStatus
* @see app/Http/Controllers/WorkflowController.php:125
* @route '/api/workflows/rules/{id}/status'
*/
toggleStatus000b49339a52f8090087438a59516318Form.put = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: toggleStatus000b49339a52f8090087438a59516318.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

toggleStatus000b49339a52f8090087438a59516318.form = toggleStatus000b49339a52f8090087438a59516318Form

/**
* Multiple routes resolve to \App\Http\Controllers\WorkflowController::toggleStatus, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `toggleStatus['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const toggleStatus = {
    '/automated/{id}/toggle': toggleStatusc72446fb39b1b3884ca40b8581d80463,
    '/api/workflows/rules/{id}/status': toggleStatus000b49339a52f8090087438a59516318,
}

/**
* @see \App\Http\Controllers\WorkflowController::destroy
* @see app/Http/Controllers/WorkflowController.php:292
* @route '/automated/{id}'
*/
const destroyad91159fa8c713556c37cc2bf4378fe1 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyad91159fa8c713556c37cc2bf4378fe1.url(args, options),
    method: 'delete',
})

destroyad91159fa8c713556c37cc2bf4378fe1.definition = {
    methods: ["delete"],
    url: '/automated/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\WorkflowController::destroy
* @see app/Http/Controllers/WorkflowController.php:292
* @route '/automated/{id}'
*/
destroyad91159fa8c713556c37cc2bf4378fe1.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return destroyad91159fa8c713556c37cc2bf4378fe1.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::destroy
* @see app/Http/Controllers/WorkflowController.php:292
* @route '/automated/{id}'
*/
destroyad91159fa8c713556c37cc2bf4378fe1.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyad91159fa8c713556c37cc2bf4378fe1.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\WorkflowController::destroy
* @see app/Http/Controllers/WorkflowController.php:292
* @route '/automated/{id}'
*/
const destroyad91159fa8c713556c37cc2bf4378fe1Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyad91159fa8c713556c37cc2bf4378fe1.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::destroy
* @see app/Http/Controllers/WorkflowController.php:292
* @route '/automated/{id}'
*/
destroyad91159fa8c713556c37cc2bf4378fe1Form.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyad91159fa8c713556c37cc2bf4378fe1.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroyad91159fa8c713556c37cc2bf4378fe1.form = destroyad91159fa8c713556c37cc2bf4378fe1Form
/**
* @see \App\Http\Controllers\WorkflowController::destroy
* @see app/Http/Controllers/WorkflowController.php:292
* @route '/api/workflows/rules/{id}'
*/
const destroyfe5c3f0ae3aa197d39f29307249517ab = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyfe5c3f0ae3aa197d39f29307249517ab.url(args, options),
    method: 'delete',
})

destroyfe5c3f0ae3aa197d39f29307249517ab.definition = {
    methods: ["delete"],
    url: '/api/workflows/rules/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\WorkflowController::destroy
* @see app/Http/Controllers/WorkflowController.php:292
* @route '/api/workflows/rules/{id}'
*/
destroyfe5c3f0ae3aa197d39f29307249517ab.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return destroyfe5c3f0ae3aa197d39f29307249517ab.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\WorkflowController::destroy
* @see app/Http/Controllers/WorkflowController.php:292
* @route '/api/workflows/rules/{id}'
*/
destroyfe5c3f0ae3aa197d39f29307249517ab.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyfe5c3f0ae3aa197d39f29307249517ab.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\WorkflowController::destroy
* @see app/Http/Controllers/WorkflowController.php:292
* @route '/api/workflows/rules/{id}'
*/
const destroyfe5c3f0ae3aa197d39f29307249517abForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyfe5c3f0ae3aa197d39f29307249517ab.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\WorkflowController::destroy
* @see app/Http/Controllers/WorkflowController.php:292
* @route '/api/workflows/rules/{id}'
*/
destroyfe5c3f0ae3aa197d39f29307249517abForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroyfe5c3f0ae3aa197d39f29307249517ab.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroyfe5c3f0ae3aa197d39f29307249517ab.form = destroyfe5c3f0ae3aa197d39f29307249517abForm

/**
* Multiple routes resolve to \App\Http\Controllers\WorkflowController::destroy, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `destroy['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const destroy = {
    '/automated/{id}': destroyad91159fa8c713556c37cc2bf4378fe1,
    '/api/workflows/rules/{id}': destroyfe5c3f0ae3aa197d39f29307249517ab,
}

const WorkflowController = { index, exportMethod, importMethod, store, execute, toggleStatus, destroy, export: exportMethod, import: importMethod }

export default WorkflowController