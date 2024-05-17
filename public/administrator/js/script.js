const accessTokenKey = "LaravelAuthenticationAdminAccessToken"

const globalState = {
    state: {
        user: null
    },

    listeners: [],

    listen (callBack) {
        this.listeners.push(callBack)
    },

    setState (newState) {
        this.state = {
            ...this.state,
            ...newState
        }

        for (let a = 0; a < this.listeners.length; a++) {
            this.listeners[a](this.state)
        }
    }
}

function bytesToUnit(bytes) {
    const sizes = ['bytes', 'KB', 'MB', 'GB', 'TB']
    if (bytes == 0) return 'byte'
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))
    return sizes[i]
}

function bytesOnlyToSize(bytes) {
    if (bytes == 0) return 0
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))
    return bytes / Math.pow(1024, i)
}

function bytesToSize(bytes) {
    const sizes = ['bytes', 'KB', 'MB', 'GB', 'TB']
    if (bytes == 0) return '0 byte'
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i]
}