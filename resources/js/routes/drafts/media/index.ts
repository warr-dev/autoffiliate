import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\PostController::upload
* @see app/Http/Controllers/PostController.php:403
* @route '/drafts/{id}/media'
*/
export const upload = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(args, options),
    method: 'post',
})

upload.definition = {
    methods: ["post"],
    url: '/drafts/{id}/media',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PostController::upload
* @see app/Http/Controllers/PostController.php:403
* @route '/drafts/{id}/media'
*/
upload.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return upload.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::upload
* @see app/Http/Controllers/PostController.php:403
* @route '/drafts/{id}/media'
*/
upload.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: upload.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::upload
* @see app/Http/Controllers/PostController.php:403
* @route '/drafts/{id}/media'
*/
const uploadForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upload.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::upload
* @see app/Http/Controllers/PostController.php:403
* @route '/drafts/{id}/media'
*/
uploadForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: upload.url(args, options),
    method: 'post',
})

upload.form = uploadForm

/**
* @see \App\Http\Controllers\PostController::deleteMethod
* @see app/Http/Controllers/PostController.php:431
* @route '/drafts/{id}/media/{filename}'
*/
export const deleteMethod = (args: { id: string | number, filename: string | number } | [id: string | number, filename: string | number ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/drafts/{id}/media/{filename}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\PostController::deleteMethod
* @see app/Http/Controllers/PostController.php:431
* @route '/drafts/{id}/media/{filename}'
*/
deleteMethod.url = (args: { id: string | number, filename: string | number } | [id: string | number, filename: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            id: args[0],
            filename: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
        filename: args.filename,
    }

    return deleteMethod.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace('{filename}', parsedArgs.filename.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::deleteMethod
* @see app/Http/Controllers/PostController.php:431
* @route '/drafts/{id}/media/{filename}'
*/
deleteMethod.delete = (args: { id: string | number, filename: string | number } | [id: string | number, filename: string | number ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\PostController::deleteMethod
* @see app/Http/Controllers/PostController.php:431
* @route '/drafts/{id}/media/{filename}'
*/
const deleteMethodForm = (args: { id: string | number, filename: string | number } | [id: string | number, filename: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: deleteMethod.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::deleteMethod
* @see app/Http/Controllers/PostController.php:431
* @route '/drafts/{id}/media/{filename}'
*/
deleteMethodForm.delete = (args: { id: string | number, filename: string | number } | [id: string | number, filename: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: deleteMethod.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

deleteMethod.form = deleteMethodForm

const media = {
    upload: Object.assign(upload, upload),
    delete: Object.assign(deleteMethod, deleteMethod),
}

export default media