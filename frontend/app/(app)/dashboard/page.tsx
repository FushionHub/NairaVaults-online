"use client"

import { useEffect } from "react"
import { useDashboardStore } from "@/store/dashboardStore"
import { Wallet, ArrowUpRight, ArrowDownRight, TrendingUp } from "lucide-react"
import Link from "next/link"

export default function DashboardPage() {
  const { fiatAccounts, cryptoWallets, recentTransactions, isLoading, fetchDashboard } =
    useDashboardStore()

  useEffect(() => {
    fetchDashboard()
  }, [fetchDashboard])

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-[60vh]">
        <div className="animate-spin w-8 h-8 border-2 border-primary border-t-transparent rounded-full" />
      </div>
    )
  }

  return (
    <div className="space-y-8">
      <h1 className="text-2xl font-bold">Dashboard</h1>

      {/* Fiat Accounts */}
      <section>
        <h2 className="text-lg font-semibold mb-4">Fiat Accounts</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {fiatAccounts.map((account) => (
            <div
              key={account.id}
              className="p-6 rounded-xl bg-card border border-border space-y-2"
            >
              <div className="flex items-center gap-2 text-muted-foreground">
                <Wallet className="w-4 h-4" />
                <span className="text-sm">{account.currency} Account</span>
              </div>
              <p className="text-2xl font-bold">
                {new Intl.NumberFormat("en-NG", {
                  style: "currency",
                  currency: account.currency,
                }).format(Number(account.balance))}
              </p>
              {account.virtual_account_number && (
                <p className="text-xs text-muted-foreground">
                  {account.virtual_account_bank} - {account.virtual_account_number}
                </p>
              )}
            </div>
          ))}
          <Link
            href="/fiat/deposit"
            className="p-6 rounded-xl border border-dashed border-border flex items-center justify-center text-muted-foreground hover:text-foreground hover:border-primary transition"
          >
            + Add Account
          </Link>
        </div>
      </section>

      {/* Crypto Wallets */}
      <section>
        <h2 className="text-lg font-semibold mb-4">Crypto Wallets</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {cryptoWallets.map((wallet) => (
            <div
              key={wallet.id}
              className="p-6 rounded-xl bg-card border border-border space-y-2"
            >
              <div className="flex items-center gap-2">
                <TrendingUp className="w-4 h-4 text-primary" />
                <span className="font-medium">{wallet.coin_symbol}</span>
              </div>
              <p className="text-xl font-bold">{wallet.balance}</p>
              {wallet.value_usd && (
                <p className="text-sm text-muted-foreground">
                  ${Number(wallet.value_usd).toFixed(2)} USD
                </p>
              )}
            </div>
          ))}
          <Link
            href="/crypto/onboarding"
            className="p-6 rounded-xl border border-dashed border-border flex items-center justify-center text-muted-foreground hover:text-foreground hover:border-primary transition"
          >
            + Add Wallet
          </Link>
        </div>
      </section>

      {/* Recent Transactions */}
      <section>
        <h2 className="text-lg font-semibold mb-4">Recent Transactions</h2>
        <div className="bg-card border border-border rounded-xl overflow-hidden">
          {recentTransactions.length === 0 ? (
            <div className="p-8 text-center text-muted-foreground">No transactions yet</div>
          ) : (
            <table className="w-full">
              <thead>
                <tr className="border-b border-border text-sm text-muted-foreground">
                  <th className="text-left p-4">Type</th>
                  <th className="text-left p-4">Amount</th>
                  <th className="text-left p-4">Status</th>
                  <th className="text-left p-4">Date</th>
                </tr>
              </thead>
              <tbody>
                {recentTransactions.map((tx) => (
                  <tr key={tx.id} className="border-b border-border last:border-0">
                    <td className="p-4 flex items-center gap-2">
                      {tx.direction === "credit" ? (
                        <ArrowDownRight className="w-4 h-4 text-green-500" />
                      ) : (
                        <ArrowUpRight className="w-4 h-4 text-red-500" />
                      )}
                      <span className="capitalize">{tx.type.replace(/_/g, " ")}</span>
                    </td>
                    <td className="p-4 font-medium">
                      {tx.direction === "credit" ? "+" : "-"}
                      {tx.amount} {tx.currency}
                    </td>
                    <td className="p-4">
                      <span
                        className={`px-2 py-1 rounded-full text-xs ${
                          tx.status === "completed"
                            ? "bg-green-500/10 text-green-500"
                            : tx.status === "failed"
                              ? "bg-red-500/10 text-red-500"
                              : "bg-yellow-500/10 text-yellow-500"
                        }`}
                      >
                        {tx.status}
                      </span>
                    </td>
                    <td className="p-4 text-sm text-muted-foreground">
                      {new Date(tx.created_at).toLocaleDateString()}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </section>
    </div>
  )
}
