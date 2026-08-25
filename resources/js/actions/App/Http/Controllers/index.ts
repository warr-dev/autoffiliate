import DashboardController from './DashboardController'
import PostController from './PostController'
import WorkflowController from './WorkflowController'
import SettingsController from './SettingsController'
import Api from './Api'
import Settings from './Settings'

const Controllers = {
    DashboardController: Object.assign(DashboardController, DashboardController),
    PostController: Object.assign(PostController, PostController),
    WorkflowController: Object.assign(WorkflowController, WorkflowController),
    SettingsController: Object.assign(SettingsController, SettingsController),
    Api: Object.assign(Api, Api),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers