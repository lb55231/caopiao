<template>
  <div class="member-list-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>会员列表</span>
          <el-button type="primary" @click="handleAdd">添加会员</el-button>
        </div>
      </template>

      <!-- 搜索筛选 -->
      <el-form :inline="true" class="search-form">
        <el-form-item label="排序">
          <el-select v-model="searchForm.ordertype" placeholder="请选择排序方式" @change="handleSearch">
            <el-option label="默认排序" :value="0" />
            <el-option label="注册时间低到高" :value="1" />
            <el-option label="彩票返点高到低" :value="2" />
            <el-option label="彩票返点低到高" :value="3" />
            <el-option label="账户金额高到低" :value="4" />
            <el-option label="账户金额低到高" :value="5" />
            <el-option label="账户积分高到低" :value="6" />
            <el-option label="账户积分低到高" :value="7" />
            <el-option label="洗码余额高到低" :value="8" />
            <el-option label="洗码余额低到高" :value="9" />
            <el-option label="登陆时间高到低" :value="16" />
            <el-option label="登陆时间低到高" :value="17" />
            <el-option label="在线时间高到低" :value="18" />
            <el-option label="在线时间低到高" :value="19" />
          </el-select>
        </el-form-item>
        <el-form-item label="会员组">
          <el-select v-model="searchForm.groupid" placeholder="请选择会员组" clearable>
            <el-option label="全部" value="" />
            <el-option
              v-for="group in groupList"
              :key="group.groupid"
              :label="group.groupname"
              :value="group.groupid"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="类型">
          <el-select v-model="searchForm.proxy" placeholder="请选择类型" clearable>
            <el-option label="全部" value="" />
            <el-option label="代理" :value="1" />
            <el-option label="会员" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="内部">
          <el-select v-model="searchForm.isnb" placeholder="请选择" clearable>
            <el-option label="全部" value="" />
            <el-option label="是" :value="1" />
            <el-option label="否" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="在线">
          <el-checkbox v-model="searchForm.isonline" :true-label="1" :false-label="0" />
        </el-form-item>
        <el-form-item label="注册时间">
          <el-date-picker
            v-model="regDateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYYMMDD"
          />
        </el-form-item>
        <el-form-item label="金额范围">
          <el-input v-model="searchForm.sAmount" placeholder="最小金额" style="width: 100px;" />
          -
          <el-input v-model="searchForm.eAmount" placeholder="最大金额" style="width: 100px;" />
        </el-form-item>
        <el-form-item label="用户名">
          <el-input v-model="searchForm.username" placeholder="请输入用户名" clearable />
        </el-form-item>
        <el-form-item label="姓名">
          <el-input v-model="searchForm.userbankname" placeholder="请输入真实姓名" clearable />
        </el-form-item>
        <el-form-item label="QQ">
          <el-input v-model="searchForm.qq" placeholder="请输入QQ" clearable />
        </el-form-item>
        <el-form-item label="昵称">
          <el-input v-model="searchForm.nickname" placeholder="请输入昵称" clearable />
        </el-form-item>
        <el-form-item label="登录IP">
          <el-input v-model="searchForm.loginip" placeholder="请输入IP" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <!-- 表格 -->
      <el-table :data="memberList" border stripe>
        <el-table-column type="selection" width="55" />
        <el-table-column prop="id" label="ID" width="60" fixed="left" />
        <el-table-column label="会员组" width="100">
          <template #default="{ row }">
            {{ getGroupName(row.groupid) }}
          </template>
        </el-table-column>
        <el-table-column prop="username" label="用户名" width="120" fixed="left" />
        <el-table-column prop="userbankname" label="姓名" width="100" />
        <el-table-column prop="parent_username" label="上线" width="100" />
        <el-table-column label="类型" width="80">
          <template #default="{ row }">
            <el-tag :type="row.proxy === 1 ? 'success' : 'info'">
              {{ row.proxy === 1 ? '代理' : '会员' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="晋级记录" width="100">
          <template #default="{ row }">
            VIP{{ row.jinjijilu || 0 }}
          </template>
        </el-table-column>
        <el-table-column label="金额" width="120">
          <template #default="{ row }">
            <el-link type="primary" @click="handleChangeBalance(row)">
              {{ row.balance }}
            </el-link>
          </template>
        </el-table-column>
        <el-table-column label="积分" width="100">
          <template #default="{ row }">
            <el-link type="primary" @click="handleChangePoint(row)">
              {{ row.point || 0 }}
            </el-link>
          </template>
        </el-table-column>
        <el-table-column label="返点" width="80">
          <template #default="{ row }">
            {{ row.fandian || 0 }}%
          </template>
        </el-table-column>
        <el-table-column label="洗码余额" width="100">
          <template #default="{ row }">
            <el-link type="primary" @click="handleChangeXima(row)">
              {{ row.xima || 0 }}
            </el-link>
          </template>
        </el-table-column>
        <el-table-column label="总充值" width="100">
          <template #default="{ row }">
            <el-link type="primary" @click="handleViewRecharge(row)">
              {{ row.total_recharge || 0 }}
            </el-link>
          </template>
        </el-table-column>
        <el-table-column label="总提款" width="100">
          <template #default="{ row }">
            <el-link type="primary" @click="handleViewWithdraw(row)">
              {{ row.total_withdraw || 0 }}
            </el-link>
          </template>
        </el-table-column>
        <el-table-column label="总输赢" width="100">
          <template #default="{ row }">
            <el-link type="primary" @click="handleViewStats(row)">
              {{ row.total_win || 0 }}
            </el-link>
          </template>
        </el-table-column>
        <el-table-column label="登录时间" width="150">
          <template #default="{ row }">
            {{ formatTime(row.logintime) }}
          </template>
        </el-table-column>
        <el-table-column prop="loginsource" label="登录来源" width="100" />
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.isonline === 1 ? 'success' : 'info'">
              {{ row.isonline === 1 ? '在线' : '离线' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="资料" width="80">
          <template #default="{ row }">
            <el-link type="primary" @click="handleViewProfile(row)">资料</el-link>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="300" fixed="right">
          <template #default="{ row }">
            <div>
              <el-link type="primary" @click="handleViewFundDetail(row)">帐变</el-link>
              <el-divider direction="vertical" />
              <el-link type="primary" @click="handleEdit(row)">编辑</el-link>
              <el-divider direction="vertical" />
              <el-link type="primary" @click="handleViewChildren(row)">下级</el-link>
            </div>
            <div style="margin-top: 5px;">
              <el-link type="danger" @click="handleDelete(row)">删</el-link>
              <el-divider direction="vertical" />
              <el-link type="warning" @click="handleKickOut(row)">踢</el-link>
              <el-divider direction="vertical" />
              <el-link
                :type="row.islock === 0 ? 'info' : 'success'"
                @click="handleToggleLock(row)"
              >
                {{ row.islock === 0 ? '锁定' : '解锁' }}
              </el-link>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div class="pagination-container">
        <el-pagination
          v-model:current-page="pagination.page"
          v-model:page-size="pagination.page_size"
          :total="pagination.total"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="loadData"
          @current-change="loadData"
        />
      </div>
    </el-card>

    <!-- 编辑对话框 -->
    <el-dialog v-model="dialogVisible" title="编辑会员" width="700px">
      <el-form :model="form" label-width="140px">
        <el-form-item label="用户名">
          <el-input v-model="form.username" disabled />
        </el-form-item>
        
        <el-form-item label="会员组">
          <el-select v-model="form.groupid" placeholder="请选择会员组">
            <el-option
              v-for="group in groupList"
              :key="group.groupid"
              :label="group.groupname"
              :value="group.groupid"
            />
          </el-select>
        </el-form-item>
        
        <el-form-item label="类型">
          <el-radio-group v-model="form.proxy">
            <el-radio :label="1">代理</el-radio>
            <el-radio :label="0">会员</el-radio>
          </el-radio-group>
        </el-form-item>
        
        <el-form-item label="返点">
          <el-input-number v-model="form.fandian" :precision="1" :min="0" :max="100" />
          <span style="margin-left: 10px;">%</span>
        </el-form-item>
        
        <el-form-item label="银行真实姓名">
          <el-input v-model="form.userbankname" placeholder="请输入银行真实姓名" />
        </el-form-item>
        
        <el-form-item label="QQ">
          <el-input v-model="form.qq" placeholder="请输入QQ" />
        </el-form-item>
        
        <el-form-item label="手机号码">
          <el-input v-model="form.tel" placeholder="请输入手机号码" />
        </el-form-item>
        
        <el-form-item label="邮箱">
          <el-input v-model="form.email" placeholder="请输入邮箱" />
        </el-form-item>
        
        <el-form-item label="密码">
          <el-input v-model="form.password" type="password" placeholder="不修改请留空" />
          <div style="color: #909399; font-size: 12px; margin-top: 5px;">不修改留空</div>
        </el-form-item>
        
        <el-form-item label="资金密码">
          <el-input v-model="form.tradepassword" type="password" placeholder="不修改请留空" />
          <div style="color: #909399; font-size: 12px; margin-top: 5px;">不修改留空</div>
        </el-form-item>
        
        <el-form-item label="邀请码">
          <el-input v-model="form.invite_code" placeholder="请输入邀请码" />
        </el-form-item>
        
        <el-form-item label="信誉分">
          <el-input-number v-model="form.xinyu" :min="0" />
        </el-form-item>
        
        <el-form-item label="提现错误次数">
          <el-input-number v-model="form.zijin_num" :min="0" />
        </el-form-item>
        
        <el-form-item label="禁止下单">
          <el-radio-group v-model="form.status_order">
            <el-radio :label="0">是</el-radio>
            <el-radio :label="1">否</el-radio>
          </el-radio-group>
        </el-form-item>
        
        <el-form-item label="禁止提款">
          <el-radio-group v-model="form.status_withdraw">
            <el-radio :label="0">是</el-radio>
            <el-radio :label="1">否</el-radio>
          </el-radio-group>
        </el-form-item>
        
        <el-form-item label="禁止提款理由">
          <el-input
            v-model="form.withdraw_deny_message"
            type="textarea"
            :rows="2"
            placeholder="请输入禁止提款理由"
          />
        </el-form-item>
        
        <el-form-item label="内部帐号">
          <el-radio-group v-model="form.isnb">
            <el-radio :label="1">是</el-radio>
            <el-radio :label="0">否</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <!-- 加减款对话框 -->
    <el-dialog v-model="balanceDialogVisible" title="余额变动" width="500px">
      <el-form :model="balanceForm" label-width="120px">
        <el-form-item label="用户名">
          <span>{{ balanceForm.username }}</span>
        </el-form-item>
        <el-form-item label="当前余额">
          <span style="color: #f56c6c; font-size: 18px; font-weight: bold;">
            {{ balanceForm.current_balance }}
          </span>
        </el-form-item>
        <el-form-item label="变动类型">
          <el-radio-group v-model="balanceForm.type">
            <el-radio label="add">增加</el-radio>
            <el-radio label="sub">减少</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="变动金额">
          <el-input-number v-model="balanceForm.amount" :precision="2" :min="0.01" />
        </el-form-item>
        <el-form-item label="变动原因">
          <el-input
            v-model="balanceForm.remark"
            type="textarea"
            :rows="3"
            placeholder="请输入变动原因"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="balanceDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleBalanceSubmit">确定</el-button>
      </template>
    </el-dialog>

    <!-- 修改积分对话框 -->
    <el-dialog v-model="pointDialogVisible" title="积分变动" width="500px">
      <el-form :model="pointForm" label-width="120px">
        <el-form-item label="用户名">
          <span>{{ pointForm.username }}</span>
        </el-form-item>
        <el-form-item label="当前积分">
          <span style="color: #409eff; font-size: 18px; font-weight: bold;">
            {{ pointForm.current_point }}
          </span>
        </el-form-item>
        <el-form-item label="变动类型">
          <el-radio-group v-model="pointForm.type">
            <el-radio label="add">增加</el-radio>
            <el-radio label="sub">减少</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="变动积分">
          <el-input-number v-model="pointForm.amount" :min="1" />
        </el-form-item>
        <el-form-item label="变动原因">
          <el-input
            v-model="pointForm.remark"
            type="textarea"
            :rows="3"
            placeholder="请输入变动原因"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="pointDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handlePointSubmit">确定</el-button>
      </template>
    </el-dialog>

    <!-- 修改洗码余额对话框 -->
    <el-dialog v-model="ximaDialogVisible" title="洗码余额变动" width="500px">
      <el-form :model="ximaForm" label-width="120px">
        <el-form-item label="用户名">
          <span>{{ ximaForm.username }}</span>
        </el-form-item>
        <el-form-item label="当前洗码余额">
          <span style="color: #67c23a; font-size: 18px; font-weight: bold;">
            {{ ximaForm.current_xima }}
          </span>
        </el-form-item>
        <el-form-item label="变动类型">
          <el-radio-group v-model="ximaForm.type">
            <el-radio label="add">增加</el-radio>
            <el-radio label="sub">减少</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="变动金额">
          <el-input-number v-model="ximaForm.amount" :precision="2" :min="0.01" />
        </el-form-item>
        <el-form-item label="变动原因">
          <el-input
            v-model="ximaForm.remark"
            type="textarea"
            :rows="3"
            placeholder="请输入变动原因"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="ximaDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleXimaSubmit">确定</el-button>
      </template>
    </el-dialog>

    <!-- 充值记录对话框 -->
    <el-dialog v-model="rechargeRecordsVisible" title="充值记录" width="1000px">
      <div class="record-header">
        <span>会员：{{ rechargeRecords.currentMember.username }}</span>
        <span style="margin-left: 30px;">总充值金额：<span style="color: #67c23a; font-weight: bold;">{{ rechargeRecords.totalAmount }}</span> 元</span>
        <span style="margin-left: 30px;">总记录数：{{ rechargeRecords.total }} 条</span>
      </div>
      
      <el-table :data="rechargeRecords.list" border stripe style="margin-top: 20px;">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="trano" label="订单号" width="180" />
        <el-table-column prop="amount" label="充值金额" width="120">
          <template #default="{ row }">
            <span style="color: #67c23a; font-weight: bold;">{{ row.amount }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="paytypetitle" label="支付方式" width="120" />
        <el-table-column prop="bankname" label="银行" width="120" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.state == 1 ? 'success' : row.state == 2 ? 'danger' : 'warning'">
              {{ row.state == 0 ? '待审核' : row.state == 1 ? '已完成' : '已拒绝' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.oddtime) }}
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" min-width="150" />
      </el-table>
      
      <el-pagination
        v-model:current-page="recordPagination.page"
        v-model:page-size="recordPagination.page_size"
        :total="rechargeRecords.total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next"
        style="margin-top: 20px; justify-content: flex-end;"
        @current-change="loadRechargeRecords(rechargeRecords.currentMember.id)"
        @size-change="loadRechargeRecords(rechargeRecords.currentMember.id)"
      />
    </el-dialog>

    <!-- 提现记录对话框 -->
    <el-dialog v-model="withdrawRecordsVisible" title="提现记录" width="1000px">
      <div class="record-header">
        <span>会员：{{ withdrawRecords.currentMember.username }}</span>
        <span style="margin-left: 30px;">总提现金额：<span style="color: #f56c6c; font-weight: bold;">{{ withdrawRecords.totalAmount }}</span> 元</span>
        <span style="margin-left: 30px;">总记录数：{{ withdrawRecords.total }} 条</span>
      </div>
      
      <el-table :data="withdrawRecords.list" border stripe style="margin-top: 20px;">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="trano" label="订单号" width="180" />
        <el-table-column prop="amount" label="提现金额" width="120">
          <template #default="{ row }">
            <span style="color: #f56c6c; font-weight: bold;">{{ row.amount }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="actualamount" label="实际金额" width="120">
          <template #default="{ row }">
            {{ row.actualamount }}
          </template>
        </el-table-column>
        <el-table-column prop="fee" label="手续费" width="100" />
        <el-table-column prop="bankname" label="银行" width="120" />
        <el-table-column prop="banknumber" label="卡号" width="180" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.state == 1 ? 'success' : row.state == 2 ? 'danger' : 'warning'">
              {{ row.state == 0 ? '待审核' : row.state == 1 ? '已完成' : '已拒绝' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.oddtime) }}
          </template>
        </el-table-column>
      </el-table>
      
      <el-pagination
        v-model:current-page="recordPagination.page"
        v-model:page-size="recordPagination.page_size"
        :total="withdrawRecords.total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next"
        style="margin-top: 20px; justify-content: flex-end;"
        @current-change="loadWithdrawRecords(withdrawRecords.currentMember.id)"
        @size-change="loadWithdrawRecords(withdrawRecords.currentMember.id)"
      />
    </el-dialog>

    <!-- 游戏记录对话框 -->
    <el-dialog v-model="gameRecordsVisible" title="游戏记录（输赢统计）" width="1200px">
      <div class="record-header">
        <span>会员：{{ gameRecords.currentMember.username }}</span>
        <span style="margin-left: 30px;">总投注：<span style="color: #409eff; font-weight: bold;">{{ gameRecords.totalBet }}</span> 元</span>
        <span style="margin-left: 30px;">总中奖：<span style="color: #67c23a; font-weight: bold;">{{ gameRecords.totalWin }}</span> 元</span>
        <span style="margin-left: 30px;">总输赢：
          <span :style="{ color: (gameRecords.totalWin - gameRecords.totalBet) >= 0 ? '#67c23a' : '#f56c6c', fontWeight: 'bold' }">
            {{ (gameRecords.totalWin - gameRecords.totalBet).toFixed(2) }}
          </span> 元
        </span>
        <span style="margin-left: 30px;">总记录数：{{ gameRecords.total }} 条</span>
      </div>
      
      <el-table :data="gameRecords.list" border stripe style="margin-top: 20px;">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="expect" label="期号" width="120" />
        <el-table-column prop="cpname" label="彩种" width="100" />
        <el-table-column prop="wanfa" label="玩法" width="120" />
        <el-table-column prop="haoma" label="号码" width="150" />
        <el-table-column prop="amount" label="投注金额" width="100">
          <template #default="{ row }">
            <span style="color: #409eff;">{{ row.amount }}</span>
          </template>
        </el-table-column>
        <el-table-column prop="okamount" label="中奖金额" width="100">
          <template #default="{ row }">
            <span style="color: #67c23a;">{{ row.okamount || 0 }}</span>
          </template>
        </el-table-column>
        <el-table-column label="输赢" width="100">
          <template #default="{ row }">
            <span :style="{ color: (row.okamount - row.amount) >= 0 ? '#67c23a' : '#f56c6c' }">
              {{ ((row.okamount || 0) - row.amount).toFixed(2) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="row.isdraw == 1 ? 'success' : row.isdraw == 2 ? 'info' : 'warning'">
              {{ row.isdraw == 0 ? '未开奖' : row.isdraw == 1 ? '已中奖' : row.isdraw == 2 ? '未中奖' : '已撤单' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="投注时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.oddtime) }}
          </template>
        </el-table-column>
      </el-table>
      
      <el-pagination
        v-model:current-page="recordPagination.page"
        v-model:page-size="recordPagination.page_size"
        :total="gameRecords.total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next"
        style="margin-top: 20px; justify-content: flex-end;"
        @current-change="loadGameRecords(gameRecords.currentMember.id)"
        @size-change="loadGameRecords(gameRecords.currentMember.id)"
      />
    </el-dialog>

    <!-- 下级会员对话框 -->
    <el-dialog v-model="childrenDialogVisible" title="下级会员列表" width="1400px">
      <div class="record-header">
        <span>上级会员：{{ childrenData.parentUsername }}</span>
        <span style="margin-left: 30px;">下级总数：{{ childrenData.total }} 人</span>
      </div>
      
      <el-table :data="childrenData.list" border stripe style="margin-top: 20px;">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="用户名" width="120" />
        <el-table-column prop="groupname" label="会员组" width="120" />
        <el-table-column label="余额" width="100">
          <template #default="{ row }">
            <span style="color: #f56c6c; font-weight: bold;">{{ row.balance }}</span>
          </template>
        </el-table-column>
        <el-table-column label="积分" width="100">
          <template #default="{ row }">
            {{ row.point || 0 }}
          </template>
        </el-table-column>
        <el-table-column label="洗码余额" width="100">
          <template #default="{ row }">
            {{ row.xima || 0 }}
          </template>
        </el-table-column>
        <el-table-column label="返点" width="80">
          <template #default="{ row }">
            {{ row.fandian || 0 }}%
          </template>
        </el-table-column>
        <el-table-column label="类型" width="80">
          <template #default="{ row }">
            <el-tag :type="row.proxy === 1 ? 'success' : 'info'">
              {{ row.proxy === 1 ? '代理' : '会员' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.islock === 0 ? 'success' : 'danger'">
              {{ row.islock === 0 ? '正常' : '锁定' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="注册时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.regtime) }}
          </template>
        </el-table-column>
        <el-table-column label="登录时间" width="180">
          <template #default="{ row }">
            {{ formatTime(row.logintime) }}
          </template>
        </el-table-column>
      </el-table>
      
      <el-pagination
        v-model:current-page="childrenPagination.page"
        v-model:page-size="childrenPagination.page_size"
        :total="childrenData.total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next"
        style="margin-top: 20px; justify-content: flex-end;"
        @current-change="loadChildren(childrenData.parentId)"
        @size-change="loadChildren(childrenData.parentId)"
      />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { useRouter } from 'vue-router'
import {
  getMemberList,
  getMemberGroups,
  updateMember,
  toggleMemberLock,
  changeMemberBalance,
  changeMemberPoint,
  changeMemberXima,
  deleteMember,
  kickOutMember,
  getMemberRechargeRecords,
  getMemberWithdrawRecords,
  getMemberGameRecords
} from '@/api/member'

const router = useRouter()
const memberList = ref([])
const groupList = ref([])
const dialogVisible = ref(false)
const balanceDialogVisible = ref(false)
const pointDialogVisible = ref(false)
const ximaDialogVisible = ref(false)
const regDateRange = ref([])

const searchForm = ref({
  username: '',
  userbankname: '',
  nickname: '',
  phone: '',
  qq: '',
  loginip: '',
  groupid: '',
  proxy: '',
  isnb: '',
  islock: '',
  isonline: 0,
  ordertype: 0,
  sDate: '',
  eDate: '',
  sAmount: '',
  eAmount: ''
})

const pagination = ref({
  page: 1,
  page_size: 20,
  total: 0
})

const form = ref({
  id: null,
  username: '',
  groupid: null,
  proxy: 0,
  fandian: 0,
  userbankname: '',
  qq: '',
  tel: '',
  email: '',
  password: '',
  tradepassword: '',
  invite_code: '',
  xinyu: 0,
  zijin_num: 0,
  status_order: 1,
  status_withdraw: 1,
  withdraw_deny_message: '',
  isnb: 0
})

const balanceForm = ref({
  uid: null,
  username: '',
  current_balance: 0,
  type: 'add',
  amount: 0,
  remark: ''
})

const pointForm = ref({
  uid: null,
  username: '',
  current_point: 0,
  type: 'add',
  amount: 0,
  remark: ''
})

const ximaForm = ref({
  uid: null,
  username: '',
  current_xima: 0,
  type: 'add',
  amount: 0,
  remark: ''
})

// 下级会员相关
const childrenDialogVisible = ref(false)
const childrenData = ref({
  parentId: null,
  parentUsername: '',
  list: [],
  total: 0
})
const childrenPagination = ref({
  page: 1,
  page_size: 20
})

const loadData = async () => {
  try {
    const params = {
      page: pagination.value.page,
      page_size: pagination.value.page_size,
      ...searchForm.value
    }
    
    if (regDateRange.value && regDateRange.value.length === 2) {
      params.sDate = regDateRange.value[0]
      params.eDate = regDateRange.value[1]
    }
    
    const res = await getMemberList(params)
    if (res.code === 200) {
      memberList.value = res.data.list
      pagination.value.total = res.data.total
    }
  } catch (error) {
    ElMessage.error('加载数据失败')
  }
}

const loadGroups = async () => {
  try {
    const res = await getMemberGroups()
    if (res.code === 200) {
      groupList.value = res.data
    }
  } catch (error) {
    console.error('加载会员组失败', error)
  }
}

const getGroupName = (groupid) => {
  const group = groupList.value.find(g => g.groupid === groupid)
  return group ? group.groupname : '-'
}

const formatTime = (timestamp) => {
  if (!timestamp) return '-'
  const date = new Date(timestamp * 1000)
  return date.toLocaleString('zh-CN', { 
    month: '2-digit', 
    day: '2-digit', 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

const handleSearch = () => {
  pagination.value.page = 1
  loadData()
}

const handleReset = () => {
  searchForm.value = {
    username: '',
    userbankname: '',
    nickname: '',
    phone: '',
    qq: '',
    loginip: '',
    groupid: '',
    proxy: '',
    isnb: '',
    islock: '',
    isonline: 0,
    ordertype: 0,
    sDate: '',
    eDate: '',
    sAmount: '',
    eAmount: ''
  }
  regDateRange.value = []
  handleSearch()
}

const handleAdd = () => {
  router.push('/member/add')
}

const handleEdit = (row) => {
  form.value = { ...row }
  dialogVisible.value = true
}

const handleSubmit = async () => {
  try {
    const res = await updateMember(form.value.id, form.value)
    if (res.code === 200) {
      ElMessage.success('修改成功')
      dialogVisible.value = false
      loadData()
    } else {
      ElMessage.error(res.msg || '修改失败')
    }
  } catch (error) {
    ElMessage.error(error.message || '修改失败')
  }
}

const handleChangeBalance = (row) => {
  balanceForm.value = {
    uid: row.id,
    username: row.username,
    current_balance: row.balance,
    type: 'add',
    amount: 0,
    remark: ''
  }
  balanceDialogVisible.value = true
}

const handleBalanceSubmit = async () => {
  try {
    if (!balanceForm.value.remark) {
      ElMessage.warning('请输入变动原因')
      return
    }
    const res = await changeMemberBalance(balanceForm.value)
    if (res.code === 200) {
      ElMessage.success('操作成功')
      balanceDialogVisible.value = false
      loadData()
    } else {
      ElMessage.error(res.msg || '操作失败')
    }
  } catch (error) {
    ElMessage.error(error.message || '操作失败')
  }
}

const handleChangePoint = (row) => {
  pointForm.value = {
    uid: row.id,
    username: row.username,
    current_point: row.point || 0,
    type: 'add',
    amount: 0,
    remark: ''
  }
  pointDialogVisible.value = true
}

const handlePointSubmit = async () => {
  try {
    if (!pointForm.value.remark) {
      ElMessage.warning('请输入变动原因')
      return
    }
    const res = await changeMemberPoint(pointForm.value)
    if (res.code === 200) {
      ElMessage.success('操作成功')
      pointDialogVisible.value = false
      loadData()
    } else {
      ElMessage.error(res.msg || '操作失败')
    }
  } catch (error) {
    ElMessage.error(error.message || '操作失败')
  }
}

const handleChangeXima = (row) => {
  ximaForm.value = {
    uid: row.id,
    username: row.username,
    current_xima: row.xima || 0,
    type: 'add',
    amount: 0,
    remark: ''
  }
  ximaDialogVisible.value = true
}

const handleXimaSubmit = async () => {
  try {
    if (!ximaForm.value.remark) {
      ElMessage.warning('请输入变动原因')
      return
    }
    const res = await changeMemberXima(ximaForm.value)
    if (res.code === 200) {
      ElMessage.success('操作成功')
      ximaDialogVisible.value = false
      loadData()
    } else {
      ElMessage.error(res.msg || '操作失败')
    }
  } catch (error) {
    ElMessage.error(error.message || '操作失败')
  }
}

const rechargeRecordsVisible = ref(false)
const withdrawRecordsVisible = ref(false)
const gameRecordsVisible = ref(false)

const rechargeRecords = ref({
  list: [],
  total: 0,
  totalAmount: 0,
  currentMember: {}
})

const withdrawRecords = ref({
  list: [],
  total: 0,
  totalAmount: 0,
  currentMember: {}
})

const gameRecords = ref({
  list: [],
  total: 0,
  totalBet: 0,
  totalWin: 0,
  currentMember: {}
})

const recordPagination = ref({
  page: 1,
  page_size: 20
})

const handleViewRecharge = async (row) => {
  try {
    rechargeRecords.value.currentMember = row
    recordPagination.value.page = 1
    await loadRechargeRecords(row.id)
    rechargeRecordsVisible.value = true
  } catch (error) {
    ElMessage.error('加载充值记录失败')
  }
}

const loadRechargeRecords = async (uid) => {
  try {
    const res = await getMemberRechargeRecords(uid, recordPagination.value)
    if (res.code === 200) {
      rechargeRecords.value.list = res.data.list
      rechargeRecords.value.total = res.data.total
      rechargeRecords.value.totalAmount = res.data.totalAmount
    } else {
      ElMessage.error(res.msg || '加载失败')
    }
  } catch (error) {
    ElMessage.error(error.message || '加载充值记录失败')
    console.error('加载充值记录失败', error)
  }
}

const handleViewWithdraw = async (row) => {
  try {
    withdrawRecords.value.currentMember = row
    recordPagination.value.page = 1
    await loadWithdrawRecords(row.id)
    withdrawRecordsVisible.value = true
  } catch (error) {
    ElMessage.error(error.message || '加载提现记录失败')
  }
}

const loadWithdrawRecords = async (uid) => {
  try {
    const res = await getMemberWithdrawRecords(uid, recordPagination.value)
    if (res.code === 200) {
      withdrawRecords.value.list = res.data.list
      withdrawRecords.value.total = res.data.total
      withdrawRecords.value.totalAmount = res.data.totalAmount
    } else {
      ElMessage.error(res.msg || '加载失败')
    }
  } catch (error) {
    ElMessage.error(error.message || '加载提现记录失败')
    console.error('加载提现记录失败', error)
  }
}

const handleViewStats = async (row) => {
  try {
    gameRecords.value.currentMember = row
    recordPagination.value.page = 1
    await loadGameRecords(row.id)
    gameRecordsVisible.value = true
  } catch (error) {
    ElMessage.error(error.message || '加载游戏记录失败')
  }
}

const loadGameRecords = async (uid) => {
  try {
    const res = await getMemberGameRecords(uid, recordPagination.value)
    if (res.code === 200) {
      gameRecords.value.list = res.data.list
      gameRecords.value.total = res.data.total
      gameRecords.value.totalBet = res.data.totalBet
      gameRecords.value.totalWin = res.data.totalWin
    } else {
      ElMessage.error(res.msg || '加载失败')
    }
  } catch (error) {
    ElMessage.error(error.message || '加载游戏记录失败')
    console.error('加载游戏记录失败', error)
  }
}

const handleViewProfile = (row) => {
  // 查看详细资料
  ElMessage.info('资料查看功能开发中')
}

const handleViewFundDetail = (row) => {
  router.push(`/member/fund-records?uid=${row.id}&username=${row.username}`)
}

const handleViewChildren = (row) => {
  // 打开下级会员弹窗
  childrenData.value.parentId = row.id
  childrenData.value.parentUsername = row.username
  childrenPagination.value.page = 1
  childrenDialogVisible.value = true
  loadChildren(row.id)
}

const loadChildren = async (parentId) => {
  try {
    const params = {
      page: childrenPagination.value.page,
      page_size: childrenPagination.value.page_size,
      parentid: parentId
    }
    
    const res = await getMemberList(params)
    if (res.code === 200) {
      childrenData.value.list = res.data.list
      childrenData.value.total = res.data.total
    } else {
      ElMessage.error(res.msg || '加载失败')
    }
  } catch (error) {
    ElMessage.error(error.message || '加载下级会员失败')
    console.error('加载下级会员失败', error)
  }
}

const handleToggleLock = async (row) => {
  try {
    const action = row.islock === 0 ? '锁定' : '解锁'
    await ElMessageBox.confirm(`确定要${action}该会员吗？`, '提示', {
      type: 'warning'
    })
    const res = await toggleMemberLock(row.id)
    if (res.code === 200) {
      ElMessage.success(`${action}成功`)
      loadData()
    } else {
      ElMessage.error(res.msg || `${action}失败`)
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('操作失败')
    }
  }
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除该会员吗？此操作不可恢复！', '警告', {
      type: 'warning',
      confirmButtonText: '确定删除',
      cancelButtonText: '取消'
    })
    const res = await deleteMember(row.id)
    if (res.code === 200) {
      ElMessage.success('删除成功')
      loadData()
    } else {
      ElMessage.error(res.msg || '删除失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

const handleKickOut = async (row) => {
  try {
    await ElMessageBox.confirm('确定要踢出该会员吗？', '提示', {
      type: 'warning'
    })
    const res = await kickOutMember(row.id)
    if (res.code === 200) {
      ElMessage.success('踢出成功')
      loadData()
    } else {
      ElMessage.error(res.msg || '踢出失败')
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('操作失败')
    }
  }
}

onMounted(() => {
  loadGroups()
  loadData()
})
</script>

<style scoped>
.member-list-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.search-form {
  margin-bottom: 20px;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.record-header {
  padding: 10px 20px;
  background: #f5f7fa;
  border-radius: 4px;
  font-size: 14px;
}
</style>
