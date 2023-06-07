import { defineStore } from "pinia";
import { Config } from "ziggy-js";

export interface ZiggyState {
  ziggyValue: Config
}

const getDomain = () => {
  const domain = (new URL(import.meta.env.VITE_BACKEND_URL));

  if (!domain) return 'localhost';

  return domain.hostname;
}

const getDomainPort = () => {
  const domain = (new URL(import.meta.env.VITE_BACKEND_URL));

  if (!domain) return 8000;

  return Number(domain.port);
}

export const useZiggyRouteStore = defineStore("ziggyRoute", {
  state: (): ZiggyState => ({
    ziggyValue: {
      url: getDomain(),
      port: getDomainPort(),
      defaults: {},
      routes: {
        'api.get.db.module.profile.read': {
          uri: 'api/get/dashboard/module/profile/read',
          methods: ['GET', 'HEAD']
        },
        'api.get.db.core.user.menu': {
          uri: 'api/get/dashboard/core/user/menu',
          methods: ['GET', 'HEAD']
        },
        'api.get.db.core.user.api': {
          uri: 'api/get/dashboard/core/user/api',
          methods: ['GET', 'HEAD']
        }
      }
    }
  }),
  getters: {
    getZiggy(state): Config {
      const serializedZiggy = sessionStorage.getItem('ziggyRoute');
      if (serializedZiggy) {
        const deserializedZiggy: Config = JSON.parse(serializedZiggy);
        this.ziggyValue = deserializedZiggy;
      }
      return state.ziggyValue;
    },
  },
  actions: {
    setZiggy(ziggy: Config) {
      sessionStorage.setItem('ziggyRoute', JSON.stringify(ziggy));
      this.ziggyValue = ziggy;
    },
  },
});