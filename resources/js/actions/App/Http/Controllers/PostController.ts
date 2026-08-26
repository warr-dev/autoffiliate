import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:21
* @route '/drafts'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/drafts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:21
* @route '/drafts'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:21
* @route '/drafts'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:21
* @route '/drafts'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:21
* @route '/drafts'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:21
* @route '/drafts'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:21
* @route '/drafts'
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
* @see \App\Http\Controllers\PostController::show
* @see app/Http/Controllers/PostController.php:438
* @route '/drafts/{id}'
*/
export const show = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/drafts/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PostController::show
* @see app/Http/Controllers/PostController.php:438
* @route '/drafts/{id}'
*/
show.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::show
* @see app/Http/Controllers/PostController.php:438
* @route '/drafts/{id}'
*/
show.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::show
* @see app/Http/Controllers/PostController.php:438
* @route '/drafts/{id}'
*/
show.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PostController::show
* @see app/Http/Controllers/PostController.php:438
* @route '/drafts/{id}'
*/
const showForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::show
* @see app/Http/Controllers/PostController.php:438
* @route '/drafts/{id}'
*/
showForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::show
* @see app/Http/Controllers/PostController.php:438
* @route '/drafts/{id}'
*/
showForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:36
* @route '/drafts'
*/
const storeedcf40b67c26e1a7099d7088201cc860 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeedcf40b67c26e1a7099d7088201cc860.url(options),
    method: 'post',
})

storeedcf40b67c26e1a7099d7088201cc860.definition = {
    methods: ["post"],
    url: '/drafts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:36
* @route '/drafts'
*/
storeedcf40b67c26e1a7099d7088201cc860.url = (options?: RouteQueryOptions) => {
    return storeedcf40b67c26e1a7099d7088201cc860.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:36
* @route '/drafts'
*/
storeedcf40b67c26e1a7099d7088201cc860.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeedcf40b67c26e1a7099d7088201cc860.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:36
* @route '/drafts'
*/
const storeedcf40b67c26e1a7099d7088201cc860Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeedcf40b67c26e1a7099d7088201cc860.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:36
* @route '/drafts'
*/
storeedcf40b67c26e1a7099d7088201cc860Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeedcf40b67c26e1a7099d7088201cc860.url(options),
    method: 'post',
})

storeedcf40b67c26e1a7099d7088201cc860.form = storeedcf40b67c26e1a7099d7088201cc860Form
/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:36
* @route '/api/posts'
*/
const storebf19ef06ce1388ecdeb1fea63820e3bd = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storebf19ef06ce1388ecdeb1fea63820e3bd.url(options),
    method: 'post',
})

storebf19ef06ce1388ecdeb1fea63820e3bd.definition = {
    methods: ["post"],
    url: '/api/posts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:36
* @route '/api/posts'
*/
storebf19ef06ce1388ecdeb1fea63820e3bd.url = (options?: RouteQueryOptions) => {
    return storebf19ef06ce1388ecdeb1fea63820e3bd.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:36
* @route '/api/posts'
*/
storebf19ef06ce1388ecdeb1fea63820e3bd.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storebf19ef06ce1388ecdeb1fea63820e3bd.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:36
* @route '/api/posts'
*/
const storebf19ef06ce1388ecdeb1fea63820e3bdForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storebf19ef06ce1388ecdeb1fea63820e3bd.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:36
* @route '/api/posts'
*/
storebf19ef06ce1388ecdeb1fea63820e3bdForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storebf19ef06ce1388ecdeb1fea63820e3bd.url(options),
    method: 'post',
})

storebf19ef06ce1388ecdeb1fea63820e3bd.form = storebf19ef06ce1388ecdeb1fea63820e3bdForm

/**
* Multiple routes resolve to \App\Http\Controllers\PostController::store, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `store['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const store = {
    '/drafts': storeedcf40b67c26e1a7099d7088201cc860,
    '/api/posts': storebf19ef06ce1388ecdeb1fea63820e3bd,
}

/**
* @see \App\Http\Controllers\PostController::storeCustom
* @see app/Http/Controllers/PostController.php:153
* @route '/posts/custom'
*/
export const storeCustom = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCustom.url(options),
    method: 'post',
})

storeCustom.definition = {
    methods: ["post"],
    url: '/posts/custom',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PostController::storeCustom
* @see app/Http/Controllers/PostController.php:153
* @route '/posts/custom'
*/
storeCustom.url = (options?: RouteQueryOptions) => {
    return storeCustom.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::storeCustom
* @see app/Http/Controllers/PostController.php:153
* @route '/posts/custom'
*/
storeCustom.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCustom.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::storeCustom
* @see app/Http/Controllers/PostController.php:153
* @route '/posts/custom'
*/
const storeCustomForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeCustom.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::storeCustom
* @see app/Http/Controllers/PostController.php:153
* @route '/posts/custom'
*/
storeCustomForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeCustom.url(options),
    method: 'post',
})

storeCustom.form = storeCustomForm

/**
* @see \App\Http\Controllers\PostController::update
* @see app/Http/Controllers/PostController.php:179
* @route '/drafts/{id}'
*/
export const update = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/drafts/{id}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\PostController::update
* @see app/Http/Controllers/PostController.php:179
* @route '/drafts/{id}'
*/
update.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::update
* @see app/Http/Controllers/PostController.php:179
* @route '/drafts/{id}'
*/
update.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\PostController::update
* @see app/Http/Controllers/PostController.php:179
* @route '/drafts/{id}'
*/
const updateForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::update
* @see app/Http/Controllers/PostController.php:179
* @route '/drafts/{id}'
*/
updateForm.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\PostController::approve
* @see app/Http/Controllers/PostController.php:200
* @route '/drafts/{id}/approve'
*/
export const approve = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

approve.definition = {
    methods: ["post"],
    url: '/drafts/{id}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PostController::approve
* @see app/Http/Controllers/PostController.php:200
* @route '/drafts/{id}/approve'
*/
approve.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return approve.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::approve
* @see app/Http/Controllers/PostController.php:200
* @route '/drafts/{id}/approve'
*/
approve.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::approve
* @see app/Http/Controllers/PostController.php:200
* @route '/drafts/{id}/approve'
*/
const approveForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::approve
* @see app/Http/Controllers/PostController.php:200
* @route '/drafts/{id}/approve'
*/
approveForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: approve.url(args, options),
    method: 'post',
})

approve.form = approveForm

/**
* @see \App\Http\Controllers\PostController::publish
* @see app/Http/Controllers/PostController.php:212
* @route '/drafts/{id}/publish'
*/
export const publish = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: publish.url(args, options),
    method: 'post',
})

publish.definition = {
    methods: ["post"],
    url: '/drafts/{id}/publish',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PostController::publish
* @see app/Http/Controllers/PostController.php:212
* @route '/drafts/{id}/publish'
*/
publish.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return publish.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::publish
* @see app/Http/Controllers/PostController.php:212
* @route '/drafts/{id}/publish'
*/
publish.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: publish.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::publish
* @see app/Http/Controllers/PostController.php:212
* @route '/drafts/{id}/publish'
*/
const publishForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: publish.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::publish
* @see app/Http/Controllers/PostController.php:212
* @route '/drafts/{id}/publish'
*/
publishForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: publish.url(args, options),
    method: 'post',
})

publish.form = publishForm

/**
* @see \App\Http\Controllers\PostController::generateCaption
* @see app/Http/Controllers/PostController.php:389
* @route '/drafts/{id}/generate-caption'
*/
export const generateCaption = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateCaption.url(args, options),
    method: 'post',
})

generateCaption.definition = {
    methods: ["post"],
    url: '/drafts/{id}/generate-caption',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PostController::generateCaption
* @see app/Http/Controllers/PostController.php:389
* @route '/drafts/{id}/generate-caption'
*/
generateCaption.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return generateCaption.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::generateCaption
* @see app/Http/Controllers/PostController.php:389
* @route '/drafts/{id}/generate-caption'
*/
generateCaption.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateCaption.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::generateCaption
* @see app/Http/Controllers/PostController.php:389
* @route '/drafts/{id}/generate-caption'
*/
const generateCaptionForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: generateCaption.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::generateCaption
* @see app/Http/Controllers/PostController.php:389
* @route '/drafts/{id}/generate-caption'
*/
generateCaptionForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: generateCaption.url(args, options),
    method: 'post',
})

generateCaption.form = generateCaptionForm

/**
* @see \App\Http\Controllers\PostController::uploadMedia
* @see app/Http/Controllers/PostController.php:449
* @route '/drafts/{id}/media'
*/
export const uploadMedia = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadMedia.url(args, options),
    method: 'post',
})

uploadMedia.definition = {
    methods: ["post"],
    url: '/drafts/{id}/media',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PostController::uploadMedia
* @see app/Http/Controllers/PostController.php:449
* @route '/drafts/{id}/media'
*/
uploadMedia.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return uploadMedia.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::uploadMedia
* @see app/Http/Controllers/PostController.php:449
* @route '/drafts/{id}/media'
*/
uploadMedia.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadMedia.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::uploadMedia
* @see app/Http/Controllers/PostController.php:449
* @route '/drafts/{id}/media'
*/
const uploadMediaForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: uploadMedia.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::uploadMedia
* @see app/Http/Controllers/PostController.php:449
* @route '/drafts/{id}/media'
*/
uploadMediaForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: uploadMedia.url(args, options),
    method: 'post',
})

uploadMedia.form = uploadMediaForm

/**
* @see \App\Http\Controllers\PostController::deleteMedia
* @see app/Http/Controllers/PostController.php:477
* @route '/drafts/{id}/media/{filename}'
*/
export const deleteMedia = (args: { id: string | number, filename: string | number } | [id: string | number, filename: string | number ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMedia.url(args, options),
    method: 'delete',
})

deleteMedia.definition = {
    methods: ["delete"],
    url: '/drafts/{id}/media/{filename}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\PostController::deleteMedia
* @see app/Http/Controllers/PostController.php:477
* @route '/drafts/{id}/media/{filename}'
*/
deleteMedia.url = (args: { id: string | number, filename: string | number } | [id: string | number, filename: string | number ], options?: RouteQueryOptions) => {
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

    return deleteMedia.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace('{filename}', parsedArgs.filename.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::deleteMedia
* @see app/Http/Controllers/PostController.php:477
* @route '/drafts/{id}/media/{filename}'
*/
deleteMedia.delete = (args: { id: string | number, filename: string | number } | [id: string | number, filename: string | number ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMedia.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\PostController::deleteMedia
* @see app/Http/Controllers/PostController.php:477
* @route '/drafts/{id}/media/{filename}'
*/
const deleteMediaForm = (args: { id: string | number, filename: string | number } | [id: string | number, filename: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: deleteMedia.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::deleteMedia
* @see app/Http/Controllers/PostController.php:477
* @route '/drafts/{id}/media/{filename}'
*/
deleteMediaForm.delete = (args: { id: string | number, filename: string | number } | [id: string | number, filename: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: deleteMedia.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

deleteMedia.form = deleteMediaForm

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:497
* @route '/drafts/{id}'
*/
const destroy710c9cd3b91985fda9cac0eabb3d3885 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy710c9cd3b91985fda9cac0eabb3d3885.url(args, options),
    method: 'delete',
})

destroy710c9cd3b91985fda9cac0eabb3d3885.definition = {
    methods: ["delete"],
    url: '/drafts/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:497
* @route '/drafts/{id}'
*/
destroy710c9cd3b91985fda9cac0eabb3d3885.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return destroy710c9cd3b91985fda9cac0eabb3d3885.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:497
* @route '/drafts/{id}'
*/
destroy710c9cd3b91985fda9cac0eabb3d3885.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy710c9cd3b91985fda9cac0eabb3d3885.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:497
* @route '/drafts/{id}'
*/
const destroy710c9cd3b91985fda9cac0eabb3d3885Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy710c9cd3b91985fda9cac0eabb3d3885.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:497
* @route '/drafts/{id}'
*/
destroy710c9cd3b91985fda9cac0eabb3d3885Form.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy710c9cd3b91985fda9cac0eabb3d3885.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy710c9cd3b91985fda9cac0eabb3d3885.form = destroy710c9cd3b91985fda9cac0eabb3d3885Form
/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:497
* @route '/api/posts/{id}'
*/
const destroybd0f2547ef8ce52742985460f62803fe = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroybd0f2547ef8ce52742985460f62803fe.url(args, options),
    method: 'delete',
})

destroybd0f2547ef8ce52742985460f62803fe.definition = {
    methods: ["delete"],
    url: '/api/posts/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:497
* @route '/api/posts/{id}'
*/
destroybd0f2547ef8ce52742985460f62803fe.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return destroybd0f2547ef8ce52742985460f62803fe.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:497
* @route '/api/posts/{id}'
*/
destroybd0f2547ef8ce52742985460f62803fe.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroybd0f2547ef8ce52742985460f62803fe.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:497
* @route '/api/posts/{id}'
*/
const destroybd0f2547ef8ce52742985460f62803feForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroybd0f2547ef8ce52742985460f62803fe.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:497
* @route '/api/posts/{id}'
*/
destroybd0f2547ef8ce52742985460f62803feForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroybd0f2547ef8ce52742985460f62803fe.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroybd0f2547ef8ce52742985460f62803fe.form = destroybd0f2547ef8ce52742985460f62803feForm

/**
* Multiple routes resolve to \App\Http\Controllers\PostController::destroy, so this export is a
* dictionary keyed by URI rather than a callable. Call a specific route with `destroy['<uri>'](...)`,
* or import the route by name from your generated `routes/` directory.
*/
export const destroy = {
    '/drafts/{id}': destroy710c9cd3b91985fda9cac0eabb3d3885,
    '/api/posts/{id}': destroybd0f2547ef8ce52742985460f62803fe,
}

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:29
* @route '/history'
*/
export const history = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:29
* @route '/history'
*/
history.url = (options?: RouteQueryOptions) => {
    return history.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:29
* @route '/history'
*/
history.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:29
* @route '/history'
*/
history.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:29
* @route '/history'
*/
const historyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:29
* @route '/history'
*/
historyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:29
* @route '/history'
*/
historyForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

history.form = historyForm

const PostController = { index, show, store, storeCustom, update, approve, publish, generateCaption, uploadMedia, deleteMedia, destroy, history }

export default PostController