// resources/js/types/inertia.d.ts
import { PageProps as InertiaPageProps } from '@inertiajs/core'

declare module '@inertiajs/vue3' {
  export interface PageProps extends InertiaPageProps {
    auth?: {
      user?: {
        id: string | number
        name: string
        email: string
      }
    }
    flash?: {
      success?: string
      error?: string
    }
    errors?: Record<string, string>
  }
}
