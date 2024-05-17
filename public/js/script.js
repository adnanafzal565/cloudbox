const accessTokenKey = "LaravelAuthenticationAccessToken"
const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"]
const monthsAbbreviated = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]

const globalState = {
    state: {
        user: null,
        files: [],
        folders: [],
        editFile: null,
        reRender: false
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
            this.listeners[a](this.state, newState)
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

function getExtension(name) {
    name = name.split(".")
    name = name[name.length - 1]
    return name
}

function dateTime(timestamp) {
    let date = new Date(timestamp)
    date = date.getDate() + " " + monthsAbbreviated[date.getMonth()] + ", " + date.getFullYear()
        + " " + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds()
        + " " + (date.getHours() >= 12 ? "pm" : "am")
    return date
}

function date(timestamp) {
    let date = new Date(timestamp)
    date = date.getDate() + " " + monthsAbbreviated[date.getMonth()] + ", " + date.getFullYear()
    return date
}