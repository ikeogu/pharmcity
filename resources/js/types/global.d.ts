import { PageProps as InertiaPageProps } from '@inertiajs/core';
import { AxiosInstance } from 'axios';
import { route as ziggyRoute } from 'ziggy-js';

declare global {
  interface Window {
    axios: AxiosInstance;
  }

  var route: typeof ziggyRoute;
  var axios: AxiosInstance;
}

declare module 'vue' {
  interface ComponentCustomProperties {
    route: typeof ziggyRoute;
    axios: AxiosInstance;
    $page: any;
  }
}

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    route: typeof ziggyRoute;
    axios: AxiosInstance;
    $page: any;
  }
}

export {};
