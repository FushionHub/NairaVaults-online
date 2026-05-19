"use client"
import { useEffect, useState } from "react"
import Link from "next/link"
import api from "@/lib/api"
import { Shield, CheckCircle, Clock, XCircle } from "lucide-react"

export default function KycPage() {
  const [kycStatus, setKycStatus] = useState<string>("unverified")
  const [record, setRecord] = useState<any>(null)

  useEffect(() => {
    api.get("/kyc/status").then(({ data }) => {
      setKycStatus(data.kyc_status)
      setRecord(data.record)
    }).catch(() => {})
  }, [])

  const statusIcon = {
    verified: <CheckCircle className="w-8 h-8 text-green-500" />,
    pending: <Clock className="w-8 h-8 text-yellow-500" />,
    rejected: <XCircle className="w-8 h-8 text-red-500" />,
    unverified: <Shield className="w-8 h-8 text-muted-foreground" />,
  }

  return (
    <div className="max-w-2xl mx-auto space-y-8">
      <h1 className="text-2xl font-bold">KYC Verification</h1>
      <div className="p-8 rounded-xl bg-card border border-border text-center space-y-4">
        {statusIcon[kycStatus as keyof typeof statusIcon]}
        <h2 className="text-xl font-semibold capitalize">{kycStatus}</h2>
        {record?.rejection_reason && (
          <p className="text-destructive text-sm">{record.rejection_reason}</p>
        )}
      </div>
      {kycStatus !== "verified" && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Link href="/kyc/individual" className="p-6 rounded-xl bg-card border border-border hover:border-primary transition text-center space-y-2">
            <Shield className="w-8 h-8 mx-auto text-primary" />
            <h3 className="font-semibold">Individual KYC</h3>
            <p className="text-sm text-muted-foreground">BVN/NIN verification</p>
          </Link>
          <Link href="/kyc/business" className="p-6 rounded-xl bg-card border border-border hover:border-primary transition text-center space-y-2">
            <Shield className="w-8 h-8 mx-auto text-primary" />
            <h3 className="font-semibold">Business KYC</h3>
            <p className="text-sm text-muted-foreground">CAC & business docs</p>
          </Link>
        </div>
      )}
    </div>
  )
}
