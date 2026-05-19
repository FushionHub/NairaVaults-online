import { create } from "zustand"
import api from "@/lib/api"

interface User {
  id: number
  email: string
  display_name: string | null
  preferred_currency: string
  kyc_status: string
  referral_code: string | null
}

interface AuthState {
  user: User | null
  token: string | null
  isLoading: boolean
  requires2fa: boolean
  pendingUserId: number | null
  setUser: (user: User | null) => void
  setToken: (token: string | null) => void
  login: (email: string, password: string, fingerprint?: string) => Promise<void>
  register: (data: RegisterData) => Promise<void>
  logout: () => Promise<void>
  verify2fa: (code: string) => Promise<void>
}

interface RegisterData {
  email: string
  password: string
  password_confirmation: string
  display_name?: string
  preferred_currency?: string
  referral_code?: string
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  token: typeof window !== "undefined" ? localStorage.getItem("auth_token") : null,
  isLoading: false,
  requires2fa: false,
  pendingUserId: null,

  setUser: (user) => set({ user }),
  setToken: (token) => {
    if (token) {
      localStorage.setItem("auth_token", token)
    } else {
      localStorage.removeItem("auth_token")
    }
    set({ token })
  },

  login: async (email, password, fingerprint) => {
    set({ isLoading: true })
    try {
      const { data } = await api.post("/auth/login", { email, password, fingerprint })

      if (data.requires_2fa) {
        set({ requires2fa: true, pendingUserId: data.user_id, isLoading: false })
        return
      }

      get().setToken(data.token)
      set({ user: data.user, isLoading: false })
    } catch {
      set({ isLoading: false })
      throw new Error("Login failed")
    }
  },

  register: async (registerData) => {
    set({ isLoading: true })
    try {
      const { data } = await api.post("/auth/register", registerData)
      get().setToken(data.token)
      set({ user: data.user, isLoading: false })
    } catch {
      set({ isLoading: false })
      throw new Error("Registration failed")
    }
  },

  logout: async () => {
    try {
      await api.post("/auth/logout")
    } finally {
      get().setToken(null)
      set({ user: null })
    }
  },

  verify2fa: async (code) => {
    set({ isLoading: true })
    try {
      const { data } = await api.post("/auth/2fa/verify", {
        user_id: get().pendingUserId,
        code,
      })
      get().setToken(data.token)
      set({ user: data.user, requires2fa: false, pendingUserId: null, isLoading: false })
    } catch {
      set({ isLoading: false })
      throw new Error("2FA verification failed")
    }
  },
}))
