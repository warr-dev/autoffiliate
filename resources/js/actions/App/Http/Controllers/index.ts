import DashboardController from './DashboardController'
import AnalyticsController from './AnalyticsController'
import PostController from './PostController'
import WorkflowController from './WorkflowController'
import SettingsController from './SettingsController'
import Api from './Api'
import Settings from './Settings'

const Controllers = {
    DashboardController: Object.assign(DashboardController, DashboardController),
    AnalyticsController: Object.assign(AnalyticsController, AnalyticsController),
    PostController: Object.assign(PostController, PostController),
    WorkflowController: Object.assign(WorkflowController, WorkflowController),
    SettingsController: Object.assign(SettingsController, SettingsController),
    Api: Object.assign(Api, Api),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers