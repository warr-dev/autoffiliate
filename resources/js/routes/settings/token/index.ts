import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\SettingsController::exchange
* @see Http/Controllers/SettingsController.php:351
* @route '/settings/token/exchange'
*/
export const exchange = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: exchange.url(options),
    method: 'post',
})

exchange.definition = {
    methods: ["post"],
    url: '/settings/token/exchange',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::exchange
* @see Http/Controllers/SettingsController.php:351
* @route '/settings/token/exchange'
*/
exchange.url = (options?: RouteQueryOptions) => {
    return exchange.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::exchange
* @see Http/Controllers/SettingsController.php:351
* @route '/settings/token/exchange'
*/
exchange.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: exchange.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::exchange
* @see Http/Controllers/SettingsController.php:351
* @route '/settings/token/exchange'
*/
const exchangeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: exchange.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::exchange
* @see Http/Controllers/SettingsController.php:351
* @route '/settings/token/exchange'
*/
exchangeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: exchange.url(options),
    method: 'post',
})

exchange.form = exchangeForm

/**
* @see \App\Http\Controllers\SettingsController::verify
* @see Http/Controllers/SettingsController.php:464
* @route '/settings/token/verify'
*/
export const verify = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(options),
    method: 'post',
})

verify.definition = {
    methods: ["post"],
    url: '/settings/token/verify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\SettingsController::verify
* @see Http/Controllers/SettingsController.php:464
* @route '/settings/token/verify'
*/
verify.url = (options?: RouteQueryOptions) => {
    return verify.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\SettingsController::verify
* @see Http/Controllers/SettingsController.php:464
* @route '/settings/token/verify'
*/
verify.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::verify
* @see Http/Controllers/SettingsController.php:464
* @route '/settings/token/verify'
*/
const verifyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: verify.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\SettingsController::verify
* @see Http/Controllers/SettingsController.php:464
* @route '/settings/token/verify'
*/
verifyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: verify.url(options),
    method: 'post',
})

verify.form = verifyForm

const token = {
    exchange: Object.assign(exchange, exchange),
    verify: Object.assign(verify, verify),
}

export default token