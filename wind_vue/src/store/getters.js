import store from "./index";


const getters = {

    getButton (state){

        return state.permission.buttons
    }
}

export default getters
