"use client"
import { useEffect, useState } from "react"
import api from "@/lib/api"
import { BarChart3, PieChart, TrendingUp } from "lucide-react"

export default function AnalyticsPage() {
  const [data, setData] = useState<any>(null)

  useEffect(() => {
    api.get("/analytics/portfolio").then(({ data }) => setData(data)).catch(() => {})
  }, [])

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold">Analytics</h1>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="p-6 rounded-xl bg-card border border-border text-center">
          <BarChart3 className="w-8 h-8 mx-auto text-primary mb-2" />
          <p className="text-2xl font-bold">{data?.total_value || "—"}</p>
          <p className="text-sm text-muted-foreground">Total Portfolio Value</p>
        </div>
        <div className="p-6 rounded-xl bg-card border border-border text-center">
          <PieChart className="w-8 h-8 mx-auto text-primary mb-2" />
          <p className="text-2xl font-bold">{data?.fiat_value || "—"}</p>
          <p className="text-sm text-muted-foreground">Fiat Holdings</p>
        </div>
        <div className="p-6 rounded-xl bg-card border border-border text-center">
          <TrendingUp className="w-8 h-8 mx-auto text-primary mb-2" />
          <p className="text-2xl font-bold">{data?.crypto_value || "—"}</p>
          <p className="text-sm text-muted-foreground">Crypto Holdings</p>
        </div>
      </div>
      <div className="p-8 rounded-xl bg-card border border-border flex items-center justify-center text-muted-foreground min-h-[300px]">
        Portfolio Performance Chart Area
      </div>
    </div>
  )
}
