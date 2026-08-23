import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:17
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
* @see app/Http/Controllers/PostController.php:17
* @route '/drafts'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:17
* @route '/drafts'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:17
* @route '/drafts'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:17
* @route '/drafts'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:17
* @route '/drafts'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::index
* @see app/Http/Controllers/PostController.php:17
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
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:32
* @route '/drafts'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/drafts',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:32
* @route '/drafts'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:32
* @route '/drafts'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:32
* @route '/drafts'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::store
* @see app/Http/Controllers/PostController.php:32
* @route '/drafts'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\PostController::storeCustom
* @see app/Http/Controllers/PostController.php:65
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
* @see app/Http/Controllers/PostController.php:65
* @route '/posts/custom'
*/
storeCustom.url = (options?: RouteQueryOptions) => {
    return storeCustom.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::storeCustom
* @see app/Http/Controllers/PostController.php:65
* @route '/posts/custom'
*/
storeCustom.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCustom.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::storeCustom
* @see app/Http/Controllers/PostController.php:65
* @route '/posts/custom'
*/
const storeCustomForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeCustom.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::storeCustom
* @see app/Http/Controllers/PostController.php:65
* @route '/posts/custom'
*/
storeCustomForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeCustom.url(options),
    method: 'post',
})

storeCustom.form = storeCustomForm

/**
* @see \App\Http\Controllers\PostController::update
* @see app/Http/Controllers/PostController.php:91
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
* @see app/Http/Controllers/PostController.php:91
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
* @see app/Http/Controllers/PostController.php:91
* @route '/drafts/{id}'
*/
update.patch = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\PostController::update
* @see app/Http/Controllers/PostController.php:91
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
* @see app/Http/Controllers/PostController.php:91
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
* @see app/Http/Controllers/PostController.php:112
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
* @see app/Http/Controllers/PostController.php:112
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
* @see app/Http/Controllers/PostController.php:112
* @route '/drafts/{id}/approve'
*/
approve.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::approve
* @see app/Http/Controllers/PostController.php:112
* @route '/drafts/{id}/approve'
*/
const approveForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: approve.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::approve
* @see app/Http/Controllers/PostController.php:112
* @route '/drafts/{id}/approve'
*/
approveForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: approve.url(args, options),
    method: 'post',
})

approve.form = approveForm

/**
* @see \App\Http\Controllers\PostController::publish
* @see app/Http/Controllers/PostController.php:124
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
* @see app/Http/Controllers/PostController.php:124
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
* @see app/Http/Controllers/PostController.php:124
* @route '/drafts/{id}/publish'
*/
publish.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: publish.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::publish
* @see app/Http/Controllers/PostController.php:124
* @route '/drafts/{id}/publish'
*/
const publishForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: publish.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::publish
* @see app/Http/Controllers/PostController.php:124
* @route '/drafts/{id}/publish'
*/
publishForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: publish.url(args, options),
    method: 'post',
})

publish.form = publishForm

/**
* @see \App\Http\Controllers\PostController::generateCaption
* @see app/Http/Controllers/PostController.php:273
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
* @see app/Http/Controllers/PostController.php:273
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
* @see app/Http/Controllers/PostController.php:273
* @route '/drafts/{id}/generate-caption'
*/
generateCaption.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: generateCaption.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::generateCaption
* @see app/Http/Controllers/PostController.php:273
* @route '/drafts/{id}/generate-caption'
*/
const generateCaptionForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: generateCaption.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::generateCaption
* @see app/Http/Controllers/PostController.php:273
* @route '/drafts/{id}/generate-caption'
*/
generateCaptionForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: generateCaption.url(args, options),
    method: 'post',
})

generateCaption.form = generateCaptionForm

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:350
* @route '/drafts/{id}'
*/
export const destroy = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/drafts/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:350
* @route '/drafts/{id}'
*/
destroy.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:350
* @route '/drafts/{id}'
*/
destroy.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:350
* @route '/drafts/{id}'
*/
const destroyForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::destroy
* @see app/Http/Controllers/PostController.php:350
* @route '/drafts/{id}'
*/
destroyForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:25
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
* @see app/Http/Controllers/PostController.php:25
* @route '/history'
*/
history.url = (options?: RouteQueryOptions) => {
    return history.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:25
* @route '/history'
*/
history.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:25
* @route '/history'
*/
history.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:25
* @route '/history'
*/
const historyForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:25
* @route '/history'
*/
historyForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: history.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\PostController::history
* @see app/Http/Controllers/PostController.php:25
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

const PostController = { index, store, storeCustom, update, approve, publish, generateCaption, destroy, history }

export default PostController