import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\PostController::custom
* @see app/Http/Controllers/PostController.php:104
* @route '/posts/custom'
*/
export const custom = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: custom.url(options),
    method: 'post',
})

custom.definition = {
    methods: ["post"],
    url: '/posts/custom',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\PostController::custom
* @see app/Http/Controllers/PostController.php:104
* @route '/posts/custom'
*/
custom.url = (options?: RouteQueryOptions) => {
    return custom.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostController::custom
* @see app/Http/Controllers/PostController.php:104
* @route '/posts/custom'
*/
custom.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: custom.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::custom
* @see app/Http/Controllers/PostController.php:104
* @route '/posts/custom'
*/
const customForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: custom.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostController::custom
* @see app/Http/Controllers/PostController.php:104
* @route '/posts/custom'
*/
customForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: custom.url(options),
    method: 'post',
})

custom.form = customForm

const posts = {
    custom: Object.assign(custom, custom),
}

export default posts