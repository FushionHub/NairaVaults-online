"use client"
import { useState, useEffect } from "react"
import api from "@/lib/api"
import { User, Shield, Bell, Smartphone } from "lucide-react"

export default function SettingsPage() {
  const [profile, setProfile] = useState<any>(null)
  const [has2fa, setHas2fa] = useState(false)

  useEffect(() => {
    api.get("/auth/profile").then(({ data }) => { setProfile(data.user); setHas2fa(data.user?.two_factor_enabled || false) }).catch(() => {})
  }, [])

  const toggle2fa = async () => {
    try {
      if (has2fa) {
        await api.post("/auth/2fa/disable")
        setHas2fa(false)
      } else {
        const { data } = await api.post("/auth/2fa/enable")
        alert(`Scan this secret: ${data.secret}`)
        setHas2fa(true)
      }
    } catch {
      alert("Failed to update 2FA")
    }
  }

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Settings</h1>
      <div className="p-6 rounded-xl bg-card border border-border space-y-4">
        <div className="flex items-center gap-2"><User className="w-5 h-5 text-primary" /><span className="font-semibold">Profile</span></div>
        {profile && (
          <div className="space-y-2 text-sm">
            <p>Email: {profile.email}</p>
            <p>Name: {profile.display_name || "Not set"}</p>
            <p>Currency: {profile.preferred_currency}</p>
            <p>KYC: {profile.kyc_status}</p>
          </div>
        )}
      </div>
      <div className="p-6 rounded-xl bg-card border border-border space-y-4">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2"><Shield className="w-5 h-5 text-primary" /><span className="font-semibold">Two-Factor Auth</span></div>
          <button onClick={toggle2fa} className={`px-4 py-2 rounded-lg text-sm ${has2fa ? "bg-destructive text-destructive-foreground" : "bg-primary text-primary-foreground"}`}>
            {has2fa ? "Disable" : "Enable"}
          </button>
        </div>
      </div>
      <div className="p-6 rounded-xl bg-card border border-border space-y-4">
        <div className="flex items-center gap-2"><Bell className="w-5 h-5 text-primary" /><span className="font-semibold">Notifications</span></div>
        <p className="text-sm text-muted-foreground">Manage notification preferences</p>
      </div>
      <div className="p-6 rounded-xl bg-card border border-border space-y-4">
        <div className="flex items-center gap-2"><Smartphone className="w-5 h-5 text-primary" /><span className="font-semibold">Trusted Devices</span></div>
        <p className="text-sm text-muted-foreground">Manage your trusted devices</p>
      </div>
    </div>
  )
}
