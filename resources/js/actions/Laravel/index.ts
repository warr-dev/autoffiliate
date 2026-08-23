import Fortify from './Fortify'
import Passkeys from './Passkeys'
import Telescope from './Telescope'

const Laravel = {
    Fortify: Object.assign(Fortify, Fortify),
    Passkeys: Object.assign(Passkeys, Passkeys),
    Telescope: Object.assign(Telescope, Telescope),
}

export default Laravel