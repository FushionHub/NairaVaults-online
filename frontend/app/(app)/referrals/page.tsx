"use client"
import { useEffect, useState } from "react"
import api from "@/lib/api"
import { Users, Copy, Share2 } from "lucide-react"

export default function ReferralsPage() {
  const [stats, setStats] = useState<any>({ referral_code: "", total_referrals: 0, total_earnings: "0", referrals: [] })

  useEffect(() => {
    api.get("/referrals").then(({ data }) => setStats(data)).catch(() => {})
  }, [])

  const copyLink = () => {
    navigator.clipboard.writeText(`${window.location.origin}/sign-up?ref=${stats.referral_code}`)
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Referral Program</h1>
      <div className="p-6 rounded-xl bg-card border border-border space-y-4">
        <div className="flex items-center gap-2">
          <Users className="w-5 h-5 text-primary" />
          <span className="font-semibold">Your Referral Code</span>
        </div>
        <div className="flex gap-2">
          <code className="flex-1 px-4 py-3 rounded-lg bg-accent font-mono text-lg">{stats.referral_code}</code>
          <button onClick={copyLink} className="px-4 py-3 rounded-lg bg-primary text-primary-foreground"><Copy className="w-4 h-4" /></button>
          <button onClick={() => navigator.share?.({ url: `${window.location.origin}/sign-up?ref=${stats.referral_code}` })}
            className="px-4 py-3 rounded-lg bg-accent"><Share2 className="w-4 h-4" /></button>
        </div>
      </div>
      <div className="grid grid-cols-2 gap-4">
        <div className="p-6 rounded-xl bg-card border border-border text-center">
          <p className="text-3xl font-bold text-primary">{stats.total_referrals}</p>
          <p className="text-sm text-muted-foreground">Total Referrals</p>
        </div>
        <div className="p-6 rounded-xl bg-card border border-border text-center">
          <p className="text-3xl font-bold text-primary">₦{Number(stats.total_earnings).toLocaleString()}</p>
          <p className="text-sm text-muted-foreground">Total Earnings</p>
        </div>
      </div>
      <div className="space-y-2">
        {(stats.referrals || []).map((r: any, i: number) => (
          <div key={i} className="p-4 rounded-lg bg-card border border-border flex justify-between items-center">
            <span className="text-sm">{r.referred_email}</span>
            <span className={`text-xs px-2 py-1 rounded-full ${r.status === "rewarded" ? "bg-green-500/10 text-green-500" : "bg-yellow-500/10 text-yellow-500"}`}>{r.status}</span>
          </div>
        ))}
      </div>
    </div>
  )
}
