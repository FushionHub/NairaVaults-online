import { create } from "zustand"
import api from "@/lib/api"

interface FiatAccount {
  id: number
  currency: string
  balance: string
  virtual_account_number: string | null
  virtual_account_bank: string | null
}

interface CryptoWallet {
  id: number
  coin_symbol: string
  public_address: string
  balance: string
  current_price?: string
  value_usd?: string
}

interface Transaction {
  id: number
  type: string
  amount: string
  currency: string
  status: string
  reference: string
  direction: string
  created_at: string
}

interface DashboardState {
  fiatAccounts: FiatAccount[]
  cryptoWallets: CryptoWallet[]
  recentTransactions: Transaction[]
  isLoading: boolean
  fetchDashboard: () => Promise<void>
  fetchFiatAccounts: () => Promise<void>
  fetchCryptoWallets: () => Promise<void>
}

export const useDashboardStore = create<DashboardState>((set) => ({
  fiatAccounts: [],
  cryptoWallets: [],
  recentTransactions: [],
  isLoading: false,

  fetchDashboard: async () => {
    set({ isLoading: true })
    try {
      const { data } = await api.get("/dashboard/summary")
      set({
        fiatAccounts: data.fiat_accounts,
        cryptoWallets: data.crypto_wallets,
        recentTransactions: data.recent_transactions,
        isLoading: false,
      })
    } catch {
      set({ isLoading: false })
    }
  },

  fetchFiatAccounts: async () => {
    try {
      const { data } = await api.get("/fiat/accounts")
      set({ fiatAccounts: data.accounts })
    } catch {
      // handled
    }
  },

  fetchCryptoWallets: async () => {
    try {
      const { data } = await api.get("/crypto/wallets")
      set({ cryptoWallets: data.wallets })
    } catch {
      // handled
    }
  },
}))
