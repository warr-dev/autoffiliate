import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see routes/web.php:77
* @route '/api/cron/workflows'
*/
export const workflows = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: workflows.url(options),
    method: 'get',
})

workflows.definition = {
    methods: ["get","head"],
    url: '/api/cron/workflows',
} satisfies RouteDefinition<["get","head"]>

/**
* @see routes/web.php:77
* @route '/api/cron/workflows'
*/
workflows.url = (options?: RouteQueryOptions) => {
    return workflows.definition.url + queryParams(options)
}

/**
* @see routes/web.php:77
* @route '/api/cron/workflows'
*/
workflows.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: workflows.url(options),
    method: 'get',
})

/**
* @see routes/web.php:77
* @route '/api/cron/workflows'
*/
workflows.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: workflows.url(options),
    method: 'head',
})

/**
* @see routes/web.php:77
* @route '/api/cron/workflows'
*/
const workflowsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: workflows.url(options),
    method: 'get',
})

/**
* @see routes/web.php:77
* @route '/api/cron/workflows'
*/
workflowsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: workflows.url(options),
    method: 'get',
})

/**
* @see routes/web.php:77
* @route '/api/cron/workflows'
*/
workflowsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: workflows.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

workflows.form = workflowsForm

const cron = {
    workflows: Object.assign(workflows, workflows),
}

export default cron