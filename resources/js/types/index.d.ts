export interface User {
  id: number
  first_name: string
  last_name: string
  email: string
  title?: string
  username?: string
  phone?: string
  dob?: string
  gender?: string
  address?: string
  role_id?: number
  country_id?: string
  state_id?: string
  city_id?: string
  country?: string
  state?: string
  city?: string
  created_at?: string  // Add this if missing
  roles?: Array<{ name: string }> | string[]
  permissions?: Array<{ name: string }>
}

export interface Drug {
  id: number
  drug_name?: string
  generic_name?: string
  drug_dose?: string
  dose_unit?: string
  drug_group_class?: string
  min_daily_dose?: number | null
  max_daily_dose?: number | null
  total_sachets_in_stock?: number
  price_per_sachet?: number
  batch_number?: string
  expiry_date?: string // ISO date string
  drug_route?: string
  is_expired?: boolean
  is_low_stock?: boolean
  // possible extra fields used by some views:
  time?: string
  patient?: string
  source?: string
  status?: string
  // for pending orders
  date_of_order?: string
  diagnosis?: string
  unit?: string
  ordered_by?: string
  consultant?: string
}


export interface Patient {
  id: number;
  hospital_id: string;
  registration_id: string;
  title: string;
  first_name: string;
  middle_name?: string;
  last_name: string;
  phone: string;
  email?: string;
  dob: string;
  gender: string;
  address?: string;
  city?: string;
  state?: string;
  country?: string;
  additional_details?: string;
  nok_name?: string;
  nok_phone?: string;
  nok_email?: string;
  nok_relationship?: string;
  nok_address?: string;
  nok_gender?: string;
}

export interface Drug {
  id: number;
  name: string;
  generic_name?: string;
  category?: string;
  manufacturer?: string;
  price: number;
  quantity: number;
  expiry_date?: string;
  [key: string]: any; // Index signature for dynamic access
}

export interface Sale {
  id: number;
  invoice_number: string;
  total: number | string;
  status: string;
}

export interface Stats {
  totalDrugs: number;
  totalPatients: number;
  totalUsers: number;
  totalSales: number | string;
}

export interface Flash {
  success?: string;
  error?: string;
}

export interface Auth {
  user: User;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
  auth: Auth;
  flash?: Flash;
};
