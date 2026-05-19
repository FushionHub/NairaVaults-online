"use client"
import { useState, useEffect } from "react"
import api from "@/lib/api"
import { Coins, Lock } from "lucide-react"

export default function StakingPage() {
  const [positions, setPositions] = useState<any[]>([])
  const [pools, setPools] = useState<any[]>([])
  const [showStake, setShowStake] = useState(false)
  const [form, setForm] = useState({ coin_symbol: "ETH", amount: "", lock_days: "30" })
  const [isLoading, setIsLoading] = useState(false)

  useEffect(() => {
    api.get("/crypto/staking").then(({ data }) => { setPositions(data.positions || []); setPools(data.pools || []) }).catch(() => {})
  }, [])

  const handleStake = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/crypto/staking", form)
      const { data } = await api.get("/crypto/staking")
      setPositions(data.positions || [])
      setShowStake(false)
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Staking</h1>
        <button onClick={() => setShowStake(!showStake)} className="px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm">
          {showStake ? "Cancel" : "Stake Now"}
        </button>
      </div>
      {showStake && (
        <form onSubmit={handleStake} className="p-6 rounded-xl bg-card border border-border space-y-4">
          <select value={form.coin_symbol} onChange={(e) => setForm((p) => ({ ...p, coin_symbol: e.target.value }))}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background">
            {["ETH", "BNB", "SOL", "MATIC", "DOT"].map((c) => <option key={c} value={c}>{c}</option>)}
          </select>
          <input type="number" value={form.amount} onChange={(e) => setForm((p) => ({ ...p, amount: e.target.value }))}
            placeholder="Amount" step="0.00000001" className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none" />
          <select value={form.lock_days} onChange={(e) => setForm((p) => ({ ...p, lock_days: e.target.value }))}
            className="w-full px-4 py-2 rounded-lg border border-input bg-background">
            {[30, 60, 90, 180, 365].map((d) => <option key={d} value={d}>{d} days</option>)}
          </select>
          <button type="submit" disabled={isLoading} className="w-full py-3 rounded-lg bg-primary text-primary-foreground disabled:opacity-50">
            {isLoading ? "Staking..." : "Stake"}
          </button>
        </form>
      )}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {positions.map((pos: any) => (
          <div key={pos.id} className="p-6 rounded-xl bg-card border border-border space-y-2">
            <div className="flex items-center gap-2"><Coins className="w-5 h-5 text-primary" /><span className="font-semibold">{pos.coin_symbol}</span></div>
            <div className="flex justify-between text-sm"><span className="text-muted-foreground">Staked</span><span>{pos.amount}</span></div>
            <div className="flex justify-between text-sm"><span className="text-muted-foreground">APY</span><span className="text-primary">{pos.apy}%</span></div>
            <div className="flex justify-between text-sm"><span className="text-muted-foreground">Rewards</span><span className="text-green-500">{pos.rewards_earned}</span></div>
            <div className="flex items-center gap-1 text-xs text-muted-foreground"><Lock className="w-3 h-3" />Locked until {new Date(pos.end_date).toLocaleDateString()}</div>
          </div>
        ))}
      </div>
      {pools.length > 0 && (
        <div>
          <h2 className="text-lg font-semibold mb-3">Available Pools</h2>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
            {pools.map((pool: any, i: number) => (
              <div key={i} className="p-4 rounded-lg bg-accent text-center">
                <p className="font-semibold">{pool.coin_symbol}</p>
                <p className="text-primary text-lg font-bold">{pool.apy}% APY</p>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
