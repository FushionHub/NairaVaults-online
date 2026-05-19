"use client"
import { useEffect, useState } from "react"
import api from "@/lib/api"
import { Medal, Check } from "lucide-react"

export default function TierPage() {
  const [tiers, setTiers] = useState<any[]>([])
  const [currentTier, setCurrentTier] = useState<any>(null)

  useEffect(() => {
    api.get("/tiers").then(({ data }) => setTiers(data.tiers || [])).catch(() => {})
    api.get("/tiers/current").then(({ data }) => setCurrentTier(data.tier)).catch(() => {})
  }, [])

  const handleUpgrade = async (tierSlug: string) => {
    try {
      await api.post("/tiers/upgrade", { tier_slug: tierSlug })
      const { data } = await api.get("/tiers/current")
      setCurrentTier(data.tier)
    } catch {
      alert("Upgrade failed")
    }
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Account Tier</h1>
      {currentTier && (
        <div className="p-6 rounded-xl bg-gradient-to-r from-primary/20 to-primary/5 border border-primary/30">
          <div className="flex items-center gap-2 mb-2">
            <Medal className="w-6 h-6 text-primary" />
            <span className="text-xl font-bold">{currentTier.name}</span>
          </div>
          <p className="text-sm text-muted-foreground">{currentTier.description}</p>
        </div>
      )}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {tiers.map((tier: any) => (
          <div key={tier.id} className={`p-6 rounded-xl border space-y-4 ${currentTier?.id === tier.id ? "border-primary bg-primary/5" : "border-border bg-card"}`}>
            <h3 className="text-lg font-bold">{tier.name}</h3>
            <p className="text-2xl font-bold">{Number(tier.upgrade_fee) > 0 ? `₦${Number(tier.upgrade_fee).toLocaleString()}` : "Free"}</p>
            <ul className="space-y-2">
              {(tier.benefits || []).map((b: string, i: number) => (
                <li key={i} className="flex items-center gap-2 text-sm"><Check className="w-3 h-3 text-primary" />{b}</li>
              ))}
            </ul>
            {currentTier?.sort_order < tier.sort_order && (
              <button onClick={() => handleUpgrade(tier.slug)}
                className="w-full py-2 rounded-lg bg-primary text-primary-foreground text-sm">Upgrade</button>
            )}
          </div>
        ))}
      </div>
    </div>
  )
}
