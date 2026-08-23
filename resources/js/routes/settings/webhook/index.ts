import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\SettingsController::test
* @see app/Http/Controllers/SettingsController.php:201
* @route '/settings/test-webhook'
*/
export const test = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: test.url(options),
    method: 'post',
})

test.definition = {
    methods: ["post"],
    url: '/settings/test-webhook',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::test
* @see app/Http/Controllers/SettingsController.php:201
* @route '/settings/test-webhook'
*/
test.url = (options?: RouteQueryOptions) => {
    return test.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::test
* @see app/Http/Controllers/SettingsController.php:201
* @route '/settings/test-webhook'
*/
test.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: test.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::test
* @see app/Http/Controllers/SettingsController.php:201
* @route '/settings/test-webhook'
*/
const testForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: test.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::test
* @see app/Http/Controllers/SettingsController.php:201
* @route '/settings/test-webhook'
*/
testForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: test.url(options),
    method: 'post',
})

test.form = testForm

const webhook = {
    test: Object.assign(test, test),
}

export default webhook