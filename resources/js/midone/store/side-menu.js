import { MENU } from "./mutation-types";
import axios from '../axios';

const state = () => {
    return {
        menu: []
    }
}

const getters = {
    menu: state => state.menu,
}

const actions = {
    setMenuContext({ commit }, menuPayload) {
        commit(MENU, { menuPayload });
    },
    fetchMenuContext({ commit }) {
        axios.get('/api/get/dashboard/core/user/menu').then(response => {
            var menuPayload = response.data;
            commit(MENU, { menuPayload });
        });
    }
}

const mutations = {
    [MENU](state, { menuPayload }) {
        state.menu = menuPayload;
    }
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}
